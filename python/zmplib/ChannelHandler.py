#
# Web-server part of channel 
#
#
# Sample HTTP header for frames, stolen from zoneminder:
#  
# Cache-Control    no-store, no-cache, must-revalidate, post-check=0, pre-check=0
# Content-Type    multipart/x-mixed-replace;boundary=ZoneMinderFrame
# Expires    Mon, 26 Jul 1997 05:00:00 GMT
# Last-Modified    Thu, 27 Sep 2012 06:04:59 GMT
# Pragma    no-cache
# Server    ZoneMinder Video Server/1.25.0

import asyncore
import cStringIO
import datetime
import logging

class ChannelHandler(asyncore.dispatcher): 
    boundary = 'ZmProxyFrame'
    chan_opened = False
    protocol_version = 'HTTP/1.0'
    count = 0
    def __init__(self, conn, addr, server, channel):
        asyncore.dispatcher.__init__(self, conn)
        self.conn = conn
        self.addr = addr
        self.server = server
        self.channel = channel
        self.queue = server.inputs[self.channel].inp.queue    
        self.frameno = 0
        self.framecount = 0
        self.id = ChannelHandler.count
        ChannelHandler.count += 1 
        self.last_sent_stamp = None
        self.last_count_stamp = None
        self.frame_to_send = None
        self.fps = 0.0
        self.buffer = ''
        self.sent_frames = 0
        self.drop_frames = 0
        self.queue.subscribe(self)
        self.logger = logging.getLogger('zmproxy.ChannelHandler')
        self.logger.debug("Channel handler %d activated. Socket handle %s" % (self.id, self._fileno))
        self.server.RegisterChan(self)
                
    def onFrameReady(self):
        if self.frameno == self.queue.ptr:
            return            
        frame, self.frameno = self.queue.getbuf(self.queue.ptr)
        
        if frame is None:
            self.logger.warn('Frame missing in source')
            return

        if not self.frame_to_send is None:
            self.drop_frames += 1

        self.frame_to_send = frame

    def writable(self):
        return len(self.buffer) > 0 or not self.frame_to_send is None

    def open_channel(self):
        code = 200
        message = 'OK'        
        head = "%s %d %s\r\n" % (self.protocol_version, code, message)
        head += 'Server: zmProxy Server/0.1\r\n'
        head += 'Date: %s\r\n' % self.getTime()
        head += 'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0\r\n'
        head += 'Content-Type: multipart/x-mixed-replace;boundary=%s\r\n' % self.boundary
        head += 'Expires: Mon, 26 Jul 1997 05:00:00 GMT\r\n'
        head += 'Last-Modified: %s\r\n' % self.getTime()
        head += 'Pragma: no-cache\r\n'
        self.buffer = head + self.buffer

        self.chan_opened = True

    def handle_write(self):
        '''write as much as possible'''
        if not self.chan_opened:
            self.open_channel()
        if len(self.buffer) == 0:
            if self.frame_to_send is None:
                self.logger.warn('handle_write(): nothing to send')
                return
            self.prepare_frame()

        sent = self.send(self.buffer)
        if sent < len (self.buffer):
            remaining = self.buffer[sent:]
            self.buffer = remaining
        else:
            self.buffer = ''
        self.last_sent_stamp = datetime.datetime.now()
#        self.logger.debug('handle_write: sent %d bytes. Buffer length %d' % (sent, len(self.buffer)))
                  
    def prepare_frame(self):
        '''Add the frame to buffer'''        
        b = '\r\n--%s\r\nContent-Type : image/jpeg\r\n' % self.boundary
        c = 'Content-Length: %s\r\n\r\n' % len(self.frame_to_send)
        self.buffer = "%s%s%s%s\r\n" % (self.buffer, b, c, self.frame_to_send)

        self.frame_to_send = None

        self.framecount += 1

        if not self.framecount % 25:
            now = datetime.datetime.now()
            s = ""
            if not self.last_count_stamp is None:
                dt = now - self.last_count_stamp
#                print dt
                sec = (dt.microseconds + dt.seconds * 10 ** 6) / 1000000.0
                self.fps = 25.0 / sec
                s = "in %f sec (%f fps)" % (sec, self.fps)
            self.last_count_stamp = now
            self.logger.debug('Channel handler %d: frame %s added to buffer %s' % (self.id, self.frameno, s))
                     
    def handle_error(self):
        self.logger.error("ChannelHandler %d: handle_error:" % self.id)
        self.logger.exception('Exception details:')         
        self.close()
        self.server.UnregisterChan(self)

    def handle_read(self):
        self.logger.debug("ChannelHandler %d: handle_read" % self.id)
        data = self.recv(4096)
        self.logger.debug('Read %s bytes: %s' % (len(data), data))
        return
    
    def handle_close(self):
        self.logger.debug('Close channel. Handle %s' % self._fileno)
        self.close()
        self.server.UnregisterChan(self)
    
    def send_header(self, keyword, value):
        """Send a MIME header."""
        self.buffer += "%s: %s\r\n" % (keyword, value)

        if keyword.lower() == 'connection':
            if value.lower() == 'close':
                self.close_connection = 1
            elif value.lower() == 'keep-alive':
                self.close_connection = 0

    def end_headers(self):
        """Send the blank line ending the MIME headers."""
        if self.request_version != 'HTTP/0.9':
            self.buffer += "\r\n"
        
    def getTime(self):
        """Return a string representation of a date according to RFC 1123
        (HTTP/1.1).
    
        The supplied date must be in UTC.
    
        """
        dt = datetime.datetime.utcnow()
        weekday = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"][dt.weekday()]
        month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep",
                 "Oct", "Nov", "Dec"][dt.month - 1]
        return "%s, %02d %s %04d %02d:%02d:%02d GMT" % (weekday, dt.day, month,
            dt.year, dt.hour, dt.minute, dt.second)
