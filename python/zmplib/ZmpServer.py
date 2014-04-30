import asyncore
import socket
import logging
import sys

class ZmProxyServer(asyncore.dispatcher):
    def __init__(self, ip, port, handler, inputs):
        self.ip = ip
        self.port = port
        self.handler = handler
        self.logger = logging.getLogger('zmproxy.server')
        self.inputs = inputs
        asyncore.dispatcher.__init__(self)
        self.create_socket(socket.AF_INET, socket.SOCK_STREAM)
        self.set_reuse_addr()
        self.logger.debug("Starting listening on %s:%s" % (ip,port))
        self.bind((ip, port))
        self.listen(5)
        self.status = "stopped"
        self.clients = {}
        
    def handle_accept(self):
        try:
            conn,addr = self.accept()
            self.logger.info( "Connection accepted from %s:%s" % addr)
        except socket.error:
            self.logger.warn("server accept() threw an exception")
            return
        except TypeError:
            t,v = sys.exc_info()[:2]
            self.logger.warn ('server accept() threw %s:%s' % (str(t), str(v)))
            return
        # creates an instance of the handler class to handle the request/response
        # on the incoming connexion
        self.status = "running"
        self.handler(conn,addr,self)

    def handle_connect(self):
        self.logger.info( "ZmProxyServer: handle_connect")

    def handle_read(self):
        self.logger.info("ZmProxyServer: handle_read")
        return

    def handle_write(self):
        self.logger.info( "ZmProxyServer: handle_write")
        return

    def handle_error(self):
        self.logger.info( "ZmProxyServer: handle_error.")
        self.logger.exception('Exception details:')         
    
    def RegisterChan(self, chan):
        '''Save info about channel for statistics'''
        self.clients[chan.id] = chan
        return
    
    def UnregisterChan(self, chan):
        if self.clients.has_key(chan.id):
            del self.clients[chan.id]
        else:
            self.logger.warning("Unregistering not registered client %d" % chan.id)
