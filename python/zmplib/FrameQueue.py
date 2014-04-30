#
#  Cyclic buffer for frames
#

class FrameQueue:
    def __init__(self, size):
        self.queue = [None for i in range(size)]
        self.size = size
        self.ptr = None
        self.len = 0
        self.clients = []
        return
    
    def addbuf(self, frame):
        if self.ptr is None:
            self.ptr = 0
        else:
            self.ptr += 1
        if self.ptr >= self.size:
            self.ptr = 0
                            
        self.queue[self.ptr] = frame
        
        if self.len < self.size:
            self.len += 1
        for c in self.clients:
            c.onFrameReady()
        return self.ptr
        
    def getbuf(self, i):
        if self.ptr is None or i < 0 or i >= self.len:
            return None, self.ptr
        if i < self.ptr:
            return self.queue[i], i+1
        elif i == self.ptr:
            return self.queue[i], i
        else:
            ret = i+1
            if ret >= self.len:
                ret = 0
            return self.queue[ret], ret        
        return self.queue[i], i # Should not happen actually
        
    def subscribe(self, client):
        self.clients.append(client)
        
    def unsubscribe(self, client):
        self.clients.remove(client)
