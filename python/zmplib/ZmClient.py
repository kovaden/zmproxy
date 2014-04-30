import asyncore
import socket
import sys
import urllib
import cookielib, urllib2
import HTMLParser
import threading
import time
import datetime
import cStringIO
import logging
from zmplib.FrameQueue import FrameQueue
from urlparse import urlparse

frame_stat_rate = 50

class LoginParser (HTMLParser.HTMLParser):
    def __init__(self):        
        HTMLParser.HTMLParser.__init__(self)
        self.is_login = False
        self.login_url = None

    def is_input_login(self, attrs):
        if ('type', 'submit') in attrs and ('value', 'Login') in attrs:
            return True

    def handle_starttag(self, tag, attrs):
        if tag == 'input':
            if self.is_input_login(attrs):
                self.is_login = True
        elif tag == 'form':
            dattr = dict(attrs)
            if dattr.has_key('name') and dattr['name'] == 'loginForm':
                if dattr.has_key('id') and dattr['id'] == 'loginForm' and dattr.has_key('action'):
                    self.is_login = True
                    self.login_url = dattr['action']
            
    def handle_endtag(self, tag):
#        print "Encountered an end tag :", tag
        return
    
    def handle_data(self, data):
#        print "Encountered some data  :", data    
        return

class ChannelPageParser(HTMLParser.HTMLParser):
    def __init__(self):        
        self.Ok = False
        HTMLParser.HTMLParser.__init__(self)
    
    def handle_startendtag(self, tag, attrs):
        if tag == 'img':
            dattr = dict(attrs)
            if dattr.has_key('id') and dattr['id'] == 'liveStream' and dattr.has_key('src'):
                self.src = dattr['src']
                self.Ok = True
                print "SRC is ", self.src
                
        HTMLParser.HTMLParser.handle_startendtag(self, tag, attrs)

class LoginHandler(threading.Thread):    
    
    def __init__(self, logger, host, path):
        self.url = "http://" + host + path
        self.logger = logger
        self.host = host
        self.path = path
        self.src = None
        self.cj = cookielib.CookieJar()
        self.opener = urllib2.build_opener(urllib2.HTTPCookieProcessor(self.cj))        
        return

    def login(self, username, password):
        auth = {'action' : 'login', 'view' : 'postlogin', 'username' : username, 'password' : password}
        r = self.opener.open(self.url)
        text = r.read()
        parser = LoginParser()
        parser.feed(text)

        self.logger.debug("Starting login process")
        if not parser.is_login:
            raise Exception('Unknown login form. Check form for submit and Login elements')

        self.logger.info( "Login form detected. Proceeding with login to: " + parser.login_url)    
        login_data = urllib.urlencode(auth)
#        print "login_data = ", login_data
        req = urllib2.Request('http://' + self.host + parser.login_url, login_data)
    
        resp = self.opener.open(req)
        text = resp.read()
#        print text
        time.sleep(0.5)
        
        req = urllib2.Request(self.url)
        resp = self.opener.open(req)
        text = resp.read()

#        print text
        cparser = ChannelPageParser()
        cparser.feed(text)
        if not cparser.Ok:
            raise Exception ('Unknown channel page')
        self.src = cparser.src
        self.logger.info("Login performed")
                
#
# HTTP client from python documentation (http://docs.python.org/library/asyncore.html)
#
class ZmClient(asyncore.dispatcher):
    boundary = '--ZoneMinderFrame'
    logger_map = {}
    def __init__(self, src):
        asyncore.dispatcher.__init__(self)
        self.id = src.id
        self.logger = self.setupLogger('zmproxy.ZmClient')
        self.src = src
        self.host = src.host
        self.path = src.path
        self.user = src.user
        self.passwd = src.passwd
        self.camera_path = ""
        self.queue = FrameQueue(src.queuesz)
        self.buffer = ""
        self.inp = ""
        self.frame_count = 0
        self.last_stamp = None
        self.chan_started = False
        self.http_started = False
        self._status = 'offline'
        self.head = {}
        self.buf = ''
        self.inp_pos = 0
        self.buffer_pos = 0
        self.fps = 0.0
        self.nconnects = 0
        self.last_count_stamp = None
        self.filehdl = None
        try:
            if self.user != None and self.user != "":
                self.login()
            self.Connect()
        except:
            self.logger.error("Camera connect FAILED");
            self.logger.info(sys.exc_info());

    def __del__(self):      
        if hasattr(self, "filehdl") and not self.filehdl is None:
            self.filehdl.close()


    def setupLogger(self, module_name):
        lname = module_name + "_%d" % self.id
        if self.logger_map.has_key(lname):
            return self.logger_map[lname]

        logger = logging.getLogger(lname)
        logger.setLevel(logging.DEBUG)
#        conshdl = logging.StreamHandler()
#        conshdl.setLevel(logging.DEBUG)
        
        self.filehdl = logging.FileHandler('zmproxy_input_%d.log' % self.id)
        self.filehdl.setLevel(logging.DEBUG)
        
        formatter = logging.Formatter('%(asctime)s %(name)s %(levelname)s %(message)s')
#        conshdl.setFormatter(formatter)
        self.filehdl.setFormatter(formatter)
#        logger.addHandler(conshdl)
        logger.addHandler(self.filehdl)
        self.logger_map[lname] = logger
        return logger

    def login(self):
        url = 'http://' + self.host  + self.path
        self.logger.info('Loging in ' + url + ' as ' + self.user)
        hdl = LoginHandler(self.logger, self.host, self.path)
        hdl.login(self.user, self.passwd)
        if hdl.src == None:
            self.logger.error('Login FAILED')
            raise Exception('Login Failed')
        self.camera_path = hdl.src
        self.logger.info('Login performed')
        return

    def Connect(self):
        ''' Connect to data socket
        '''
        
        self.buffer = "GET %s\r\n\r\n" % self.camera_path
                
        self.create_socket(socket.AF_INET, socket.SOCK_STREAM)
        self.logger.debug('Connecting to ' + self.host + ' port 80')
        self.connect((self.host, 80))

    def parse_packet(self, in_str):       
        ''' Receive packet starting with boundary, to the end start of next boundary
        '''
                
        packet = cStringIO.StringIO(in_str)
        content_type = None
        content_length= None
        in_head = False
        while 1:
            line=packet.readline().strip()
            if len(line) == 0:
                if not in_head:
                    in_head = True
                    continue
                else:
                    break;
                
            if line == self.boundary:
                # Found next boundary - give up
                in_head = True
                continue
                        
            parts = line.split(':')
            if len(parts) == 2:
                if parts[0] == 'Content-Type':
                    content_type = parts[1].strip()
                elif parts[0] == 'Content-Length':
                    content_length = int(parts[1])
        
        buf = packet.read(content_length)
        if content_type != 'image/jpeg':
            self.logger.warning( "ZmClient: warning: wrong packet type. Got %s, expected image/jpeg " % content_type)
        if len (buf) != content_length:
            self.logger.warning( "ZmClient: warning: packet of wrong size. Got %s bytes, expected from header %s" % (len(buf), content_length))
            db = len(buf) - content_length
            self.logger.debug( "Extra bytes: %d (%s)" % (len(buf[-db:]), ' '.join( [hex(ord(x)) for x in buf[-db:] ])))
        packet.close()
#        self.logger.debug("Packet received: %d bytes" % len(buf))
        return buf    

    def status(self):
        if self._status == 'Ok':
            if not self.last_stamp is None:
                datediff = datetime.datetime.now() - self.last_stamp
                if datediff > datetime.timedelta(0, 60):            
                    self._status = 'hang'
            else:
                self._status = 'no signal'
    
        return self._status

    def start_http(self):        
        ''' Read HTTP header(s) and check if we are listening for the right source
        '''
        packet = cStringIO.StringIO(self.inp)
        while 1:
            line = packet.readline().strip()
            if not line:
                break
            self.logger.debug( "ZmClient: recieved header line: %s" % line)
            if line == 'HTTP/1.0 200 OK':                
                self.http_started = True
                pos = packet.tell()
                self.inp = self.inp[pos:]
                self._status = "Ok"
            else:
                self.logger.debug( 'ZmClient error: wrong HTTP header line: %s' % line)
#                self.logger.debug( packet.getvalue() )
                self._status = "error"
            return

    def start_channel(self):        
        ''' Read HTTP header(s) and check if we are listening for the right source
        '''
        packet = cStringIO.StringIO(self.inp)
        while 1:
            line = packet.readline().strip()
            if not line:
                break
            self.logger.debug( "ZmClient: recieved header line: %s" % line)
            parts = line.split(':')
            self.head[parts[0]] = parts[1]
        if self.head.has_key('Content-Type'):
            self.chan_started = True
            self.inp = ''

    def stream_process(self):
        fr = self.inp.find(self.boundary)
        out_pos = None
        while fr != -1:        
            self.inp_pos = fr + len(self.boundary)
            fr1 = self.inp.find(self.boundary, self.inp_pos)
            if fr1 != -1:                            
                frame = self.parse_packet(self.inp[fr: fr1])
                if not frame is None:
                    self.buffer_pos = self.queue.addbuf(frame)
                    self.frame_count += 1
                    self.last_stamp = datetime.datetime.now()
                    if not self.frame_count % frame_stat_rate:
                        now = datetime.datetime.now()                        
                        s = ""
                        if not self.last_count_stamp is None:
                            dt = now - self.last_count_stamp
            #                print dt
                            sec = (dt.microseconds + dt.seconds * 10**6) / 1000000.0
                            self.fps = float(frame_stat_rate)/sec
                            s = " %f sec (%f fps)" % (sec, self.fps)
                        self.last_count_stamp = now                    
                        
                        self.logger.debug( "ZmClient %s: %sth Frame processed. Length %s total count %s %s" % (self.host, frame_stat_rate, len(frame), self.frame_count, s))
                        if self.status() != "Ok":
                            self.logger.info("Camera connection restored")
                            self._status = "Ok"
                        f = open('bee.jpg', 'w')
                        f.write(frame)
                        f.close()
                    out_pos = fr1        
            fr = fr1    
        if out_pos:
            self.inp = self.inp[out_pos:]
        return

    def handle_connect(self):
        self.logger.debug( "Connected. Sending %s" % self.buffer)
        self.nconnects += 1
        pass

    def handle_close(self):
        self.logger.debug('Closing socket')
        self.close()
        self._status = 'disconnected'
        #
        #  As we don't support 'normal' shutdown, closing socket is not expected operation. Try to reconnect it.
        #
        
    def handle_error(self):        
        self.logger.error("ChannelHandler %d: handle_error." % self.id)
        self.logger.exception('Exception details:')         
        self.close()
        self._status = 'disconnected'
        
    def handle_read(self):
        buf = self.recv(8192)
#        self.inp = ''.join([self.inp, buf])
        self.inp = '%s%s' % (self.inp, buf)

        if not self.http_started:
            self.start_http()
            return
            
        if not self.chan_started:
            self.start_channel()
            return
            
        self.stream_process()

    def writable(self):
        return (len(self.buffer) > 0)

    def handle_write(self):
        sent = self.send(self.buffer)
        self.buffer = self.buffer[sent:]
        
    def start(self):
        return
    
    def stop(self):
        return
    
    def join(self):
        return
