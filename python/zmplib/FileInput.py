#
#  ZmProxy test input for reading from file system
#   It reads jpeg files from a directoty in a loop and places to proxy buffer  
#   Parameters are: 
#      - input directory
#      - frame interval, ms
#

import threading
import os
import re
import time
import logging
import datetime
from zmplib.FrameQueue import FrameQueue

def is_jpeg(fname):
    return re.match("^.*\.jpe?g", fname)

class FileInput(threading.Thread):
    def __init__(self, inpid, queuesz, path, interval_ms):
        self.id = inpid
        self.path = path
        self.interval = interval_ms        
        self.files = filter(is_jpeg, os.listdir(self.path))
        self.currfile = 0
        self.Exit = False
        self.queue = FrameQueue(queuesz)
        self.last_stamp = None
        self.host = None
        self.frame_count = 0
        self._status = "stopped"
        self.fps = 0.0
        self.buffer_pos = 0
        self.nconnects = 0
        self.logger = logging.getLogger('zmproxy.FileInput')
        threading.Thread.__init__(self)
        self.logger.info( "File input: %s files to process" % len(self.files))
        
    def status(self):
        return self._status
        
    def proc_file(self):
        f = open(self.path + "/" + self.files[self.currfile])
        buf = f.read()
        self.buffer_pos = self.queue.addbuf(buf)
        f.close()
        self.frame_count += 1
        self.currfile += 1
        if self.currfile >= len(self.files):
            self.currfile = 0
        self.last_stamp = datetime.datetime.now()
        if self.frame_count % 100 == 0:
            self.logger.info( "File input: frame processed (%s bytes). Frame count = %s, currfile = %s" % (len(buf), self.frame_count, self.currfile))
        
    def run(self):
        self.logger.info( "Start FileInput with interval %s ms." % self.interval) 
        self._status = "Ok"
        while not self.Exit:
            self.proc_file()
            time.sleep(self.interval / 1000.0)
        self._status = "stopped"
        return    
    
    def stop(self):
        self.Exit = True
        