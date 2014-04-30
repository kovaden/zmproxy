import asynchat
import StringIO
import traceback
import sys
import re
import BaseHTTPServer
import StatsHandler
import logging
from zmplib.ChannelHandler import ChannelHandler

service_ips = ['127.0.0.1', '10.3.7.']

ZmNotFoundHTML = '''
<HTML>
<HEAD>
<title>ZmProxy: page not handled</title>
</HEAD>
<BODY>
<H1><font  color='red'>Page not handled</font></H1>
</BODY>
</HTML>
'''

ZmNotAllowedHTML = '''
<HTML>
<HEAD>
<title>ZmProxy: service not allowed</title>
</HEAD>
<BODY>
<H1><font  color='red'>Service not allowed</font></H1>
</BODY>
</HTML>
'''

bootstrap_css = '''

html {
    font-size: 100%;
}

body {
    background-color: #FFFFFF;
    color: #333333;
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    font-size: 13px;
    line-height: 18px;
    margin: 0;
}
h1, h2, h3, h4, h5, h6 {
    color: inherit;
    font-family: inherit;
    font-weight: bold;
    margin: 0;
    text-rendering: optimizelegibility;
}
h1 {
    font-size: 30px;
    line-height: 36px;
}
h2 {
    font-size: 24px;
    line-height: 36px;
}
small {
    font-size: 100%;
}
a {
  color: #0088cc;
  text-decoration: none;
}

a:hover {
  color: #005580;
  text-decoration: underline;
}
p {
  margin: 0 0 9px;
}
ul,
ol {
  padding: 0;
  margin: 0 0 9px 25px;
}
ul {
  list-style: disc;
}

ol {
  list-style: decimal;
}

li {
  line-height: 18px;
}
hr {
  margin: 18px 0;
  border: 0;
  border-top: 1px solid #eeeeee;
  border-bottom: 1px solid #ffffff;
}

.container-fluid {
    padding-left: 20px;
    padding-right: 20px;
}
.container-fluid:after,
.row-fluid:after {
    clear: both;
}
.container-fluid:before, .container-fluid:after,
.row-fluid:before, .row-fluid:after {
    content: "";
    display: table;
}
.row-fluid {
    width: 100%;
}
.page-header {
    border-bottom: 1px solid #EEEEEE;
    margin: 18px 0;
    padding-bottom: 17px;
}
.page-header h1 {
    line-height: 1;
}

table {
    background-color: transparent;
    border-collapse: collapse;
    border-spacing: 0;
    max-width: 100%;
    margin-bottom: 18px;
    width: 100%;
}
thead:first-child tr:first-child th, thead:first-child tr:first-child td {
    border-top: 0 none;
}
thead th {
    vertical-align: bottom;
}
th {
    font-weight: bold;
}
th, td {
    border-top: 1px solid #DDDDDD;
    line-height: 18px;
    padding: 8px;
    text-align: left;
    vertical-align: top;
}
.table-striped tbody tr:nth-child(2n+1) td, .table-striped tbody tr:nth-child(2n+1) th {
    background-color: #F9F9F9;
}

'''

def check_addr(addr):
    for allowed in service_ips:
        if addr.find(allowed) == 0:
            return True
    return False

class RequestHandler(asynchat.async_chat, BaseHTTPServer.BaseHTTPRequestHandler):
    protocol_version = "HTTP/1.1"
    server_version = "ZmProxyHTTPServer/0.0.1"
    
    def __init__(self, conn, addr, server):
        asynchat.async_chat.__init__(self, conn)
        self.client_address = addr
        self.connection = conn
        self.server = server
        self.logger = logging.getLogger('zmproxy.RequestHandler')
        self.logger.info('RequestHandler started. Socket handle %s' % self._fileno)
        # set the terminator : when it is received, this means that the
        # http request is complete ; control will be passed to
        # self.found_terminator
        self.set_terminator ('\r\n\r\n')
        self.rfile = StringIO.StringIO()
        self.found_terminator = self.handle_request_line
        self.request_version = "HTTP/1.1"
        self.wfile = StringIO.StringIO()
        
    def collect_incoming_data(self, data):
        """Collect the data arriving on the connection"""
        self.rfile.write(data)
        
    def address_string(self):
        """Return the client address formatted for logging.
        """
        host, port = self.client_address[:2]
        return host
        
        
    def prepare_POST(self):
        """Prepare to read the request body"""
        bytesToRead = int(self.headers.getheader('content-length'))
        # set terminator to length (will read bytesToRead bytes)
        self.set_terminator(bytesToRead)
        self.rfile = cStringIO.StringIO()
        # control will be passed to a new found_terminator
        self.found_terminator = self.handle_post_data

    def handle_post_data(self):
        """Called when a POST request body has been read"""
        self.rfile.seek(0)
        self.do_POST()
        self.finish()
        
    def do_GET(self):
        """Begins serving a GET request"""
        # nothing more to do before handle_data()
        self.body = {}
        self.handle_get_data()                    

    def handle_data(self):
        self.logger.debug( "handle_data")

    def handle_get_data(self):
        self.logger.debug( "Get request: %s" % self.path)

        content_type = 'text/html'

        if self.path == '/' or self.path == '/status.htm':
            if check_addr( self.client_address[0] ):
                self.logger.info( "Display status page to %s" % self.client_address[0])
                hdl = StatsHandler.StatsHandler(self.server)
                body = hdl.handle().encode('utf-8')
                hdl.close()
            else:
                self.logger.info( "Status page denied to %s" % self.client_address[0])
                body = ZmNotAllowedHTML
        elif self.path == '/bootstrap.css':
            body = bootstrap_css
            content_type = 'text/css'
        else:
            m=re.search("^/chan=([0-9]+)", self.path)
            if m:
                chan=m.group(1)
                self.logger.info( "Channel %s requested to %s" % (chan, self.client_address[0]))
                body = "channel %s!!!!" % chan                
                self.del_channel()
                self.chanhdl = ChannelHandler(self.connection, self.addr, self.server, int(chan))                         
                return
            else:
                self.logger.info( "Page %s not found( client %s)" % (self.path, self.client_address[0]))
                body = ZmNotFoundHTML
        
        length = len(body)
        
        self.send_response(200)
        encoding = sys.getfilesystemencoding()
        self.send_header("Content-type", "%s" % content_type)
        self.send_header("Content-Length", str(length+2))
        self.end_headers()
        
        if self.request_version != 'HTTP/0.9':
            self.wfile.write("\r\n")
#        self.send(self.wfile.getvalue())
        
#        self.logger.debug ('RequestHandler:handle_data: prepare body (%s bytes)' % length)
  
        self.wfile.write(body)
        self.wfile.write("\r\n")
                
    def handle_request_line(self):
        """Called when the http request line and headers have been received"""
        # prepare attributes needed in parse_request()
        self.rfile.seek(0)
        self.raw_requestline = self.rfile.readline()
        self.parse_request()
        
        if self.command in ['GET','HEAD']:
            # if method is GET or HEAD, call do_GET or do_HEAD and finish
            method = "do_"+self.command
            if hasattr(self,method):
                getattr(self,method)()
                if not hasattr(self, 'chanhdl'):
                    self.finish()
        elif self.command=="POST":
            # if method is POST, call prepare_POST, don't finish yet
            self.prepare_POST()
        else:
            self.send_error(501, "Unsupported method (%s)" %self.command)
                
    def handle_error(self):
        self.logger.error(traceback.format_exc())
        self.close()

    def finish(self):
        """Send data, then close"""
        try:
            buffer = self.wfile.getvalue()
            self.push(buffer)
            self.logger.debug( "RequestHandler:finish() Buffer length %s, %s ...  %s" % (len(buffer), buffer[:15], buffer[-15:]))
            self.logger.debug("buffer size is %s" % self.ac_out_buffer_size)
        except AttributeError: 
            self.logger.error( "AttributeError")
            self.logger.error(traceback.format_exc())
            # if end_headers() wasn't called, wfile is a StringIO
            # this happens for error 404 in self.send_head() for instance
            self.wfile.seek(0)
        self.close_when_done()
                                
