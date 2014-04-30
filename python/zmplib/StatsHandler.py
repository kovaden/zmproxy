#
#   Display statistics for ZmPrxy
#

import StringIO
import datetime

class StatsHandler:
    def __init__(self, zmproxy):
        self.wfile = StringIO.StringIO()
        self.title = "ZMProxy web interface"
        self.zmproxy = zmproxy 
        self.logger = zmproxy.logger
        return
    
    def close(self):
        self.wfile.close()
    
    def head(self):
        self.wfile.write ('''        <head>
          <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
          <meta charset="utf-8">
          <title>%s (%s)</title>
          <link href="/bootstrap.css" rel="stylesheet">
          <!-- meta http-equiv="refresh" content="10" -->
          </head>
        ''' % (self.title, self.zmproxy.status)
        )

    def h1(self, s, descr = None):
        self.wfile.write('<div class="page-header">')
        self.wfile.write("<h1>%s</h1>" % s)
        if not descr is None:
            self.wfile.write('<small>%s</small>'% descr)
        self.wfile.write('</div>')

    def h2(self, s):
        self.wfile.write("<h2>%s</h2>" % s)

    def tbl(self, tbl):
        '''
        Write HTML table to output stream
        input in format:
            
            { 
               head: ['head1', 'head2', ...], #optional
               rowhead: ['val1', 'val2', ...],
               ...
            }
        '''            
        self.wfile.write('<table class="table-striped">')
        if 'head' in tbl.keys():
            self.wfile.write('<thead>')
            self.wfile.write('<tr>')
            for val in tbl['head']:
                self.wfile.write('<th>%s</th>' % val)
            self.wfile.write('</tr>')
            self.wfile.write('</thead>')
            
        self.wfile.write('<tbody>')
        for key in tbl:
            if key == 'head':
                continue
            self.wfile.write('<tr>')
            self.wfile.write('<th>%s</th>' % key)
            for val in tbl[key]:
                self.wfile.write('<td>%s</td>' % val)
            self.wfile.write('</tr>')
        self.wfile.write('</tbody>')
        self.wfile.write('</table>')
        
    def body(self):
        self.wfile.write('<body>')
        self.wfile.write('<div  class="container-fluid">')
        self.wfile.write('<div class="row-fluid">')
        self.h1('ZmProxy status')
        self.tbl({
                  'head'   : ['param', 'value'],
                  'Status' : [self.zmproxy.status], 
                  })
                
        self.h2('Input channels')
        tbl = {'head' : ['title', 'id', 'host', 'input path', 'link', 'buffer pos', 'frame count', 'last_frame', 'fps', 'nconnects', 'status']}
        for i in self.zmproxy.inputs:
            key = self.zmproxy.inputs[i].name
            inp = self.zmproxy.inputs[i].inp
            if not inp.last_stamp is None:
                datediff = datetime.datetime.now() - inp.last_stamp
            else:
                datediff = None                          
            link = '<a href="/chan=%s">/chan=%s</a>' % (inp.id, inp.id)
            inp_path = '<a href="http://%s%s">%s</a>' %(inp.host, inp.path, inp.path)
            tbl[key] = [inp.id, inp.host, inp_path, link, inp.buffer_pos, inp.frame_count, datediff, inp.fps, inp.nconnects, inp.status()]
        self.tbl(tbl)
        
        self.h2('Clients (%s)' % len(self.zmproxy.clients))
        ct = {'head' : ['id', 'ip', 'port', 'channel', 'buffer pos', 'buffer length', 'frames sent', 'frames drop', 'fps']}
        for id in self.zmproxy.clients:
            client = self.zmproxy.clients[id]            
            ct[id] = [client.addr[0], client.addr[1], client.channel, client.frameno, len(client.buffer), client.framecount, client.drop_frames,  client.fps]            
        self.tbl(ct)
        
        self.wfile.write('</div></div></body>')
        return
    
    def handle(self):
        self.wfile.write('<!DOCTYPE html>\r\n')
        self.wfile.write('<html>\r\n')
        self.head()
        self.body()
        self.wfile.write('</html>')
        return self.wfile.getvalue()
