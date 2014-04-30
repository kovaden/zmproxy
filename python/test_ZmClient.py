import unittest
import zmplib
from zmplib import ZmClient

class TestLoginHandler(unittest.TestCase):
    def setUp(self):
        url = "http://bee/zm/index.php?view=watch&mid=1"
        self.hdl = ZmClient.LoginHandler(url)

    def test_login(self):
        self.hdl.login('admin', 'admin')     
        return
        
    def tearDown(self):
        unittest.TestCase.tearDown(self)

class TestZmConnect(unittest.TestCase):
    def setUp(self):
        auth_ip = '192.168.1.103'
        src = zmplib.Source(id=1, type='zm', name='bee', host='bee', 
                                path='/zm/index.php?view=watch&mid=1',
                                interval=None, queuesz=100, 
                                user='admin', passwd='admin')         
        self.client = ZmClient.ZmClient(src)
        return
    
    def test_parse_login(self):
        print "Parse login"
        return
    
    def tearDown(self):
        unittest.TestCase.tearDown(self)
    
if __name__ == '__main__':
    unittest.main()