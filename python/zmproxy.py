#!/usr/bin/python

import asyncore
import traceback
import logging
import datetime
import xmlrpclib
import zmplib
from zmplib import ZmClient
from zmplib import RequestHandler
from zmplib.ZmpServer import ZmProxyServer
from zmplib.FileInput import FileInput
from collections import namedtuple
from zmpconfig import sources
from urlparse import urlparse
import zmpconfig

import statprof

class Input:
	def __init__(self, _id, name, inp):
		self.id = _id
		self.name = name
		self.inp = inp

def split_url(url):
	''' Split url to host and path parts'''
	res = urlparse(url)
	if res[4] == "":
		path = res[2]
	else:
		path = "%s?%s" % (res[2], res[4])
	if res.port:
		port = res.port
	else:
		port = 80
	return res.hostname, port, path

def run():

	logger = logging.getLogger('zmproxy')
	logger.setLevel(logging.DEBUG)
	
	conshdl = logging.StreamHandler()
	conshdl.setLevel(logging.DEBUG)
	
	filehdl = logging.FileHandler('zmproxy.log')
	filehdl.setLevel(logging.DEBUG)
	
	formatter = logging.Formatter('%(asctime)s %(name)s %(levelname)s %(message)s')
	conshdl.setFormatter(formatter)
	filehdl.setFormatter(formatter)
	logger.addHandler(conshdl)
	logger.addHandler(filehdl)

	logger.setLevel(logging.DEBUG)

	inputs = {}
	if not zmpconfig.use_local_sources:
		print 'Connecting XMLRPC to ', zmpconfig.cam_source_server
		xmlrpc_server = xmlrpclib.Server(zmpconfig.cam_source_server)
		camlist = xmlrpc_server.ReadCameraList()
		
		print "Read camera list from server. %s cameras:" % len(camlist)
		for cam in camlist:
			print cam
			if cam['down'] == '1':
				logger.info('Camera %s (%s) disabled in config' % (cam['id'], cam['name']));
				continue
			host,port,path = split_url(cam['url'])
			src = zmplib.Source(
				id=int(cam['id']),   
				type='zm',     
				name=cam['name'], 
				host=host,
				port=port,
				path=path, 
				interval=None,    
				queuesz=int(cam['queuesz']),   
				user=cam['user'],  
				passwd=cam['passwd'])

			inp = ZmClient.ZmClient(src)
			inputs[src.id] = Input(src.id, src.name, inp)
		
	else:
		for src in sources:
			if src.type == 'zm':
				inp = ZmClient.ZmClient(src)
			elif src.type == 'file':
				inp = FileInput(src.id, src.queuesz, src.path, src.interval)
			inputs[src.id] = Input(src.id, src.name, inp) 
		
	server = ZmProxyServer('', zmpconfig.port, RequestHandler.RequestHandler, inputs)
	
	try:
		niters = 10000
		for k in inputs.keys():
			inp = inputs[k]
			inp.inp.start()
			
		logger.info("Init sources done. (%d sources) Processing..." % len(inputs))
		start = datetime.datetime.now()
		while True:
			asyncore.loop(timeout=0.05, use_poll=False, map=None, count=niters)
			logger.info("Checking sources...")
			for i in inputs:
				inp = inputs[i]
				
				if inp.inp.status() != 'Ok':
					src = inp.inp.src
					nconnects = inp.inp.nconnects
					logger.warn("Channel %d status %s. Trying to reconnect, %d attempts so far..." % (inp.inp.id, inp.inp.status(), nconnects))
					s = inp.inp
					del s
					inp.inp = ZmClient.ZmClient(src)
					inp.inp.nconnects = nconnects + 1
			
		end = datetime.datetime.now()
		print "Finished. %d iterations done in %s sec" % (niters, end - start)
	except KeyboardInterrupt:
		logger.info("Ctrl+C pressed. Shutting down the server")
	except:
		traceback.print_exc()
	finally:
		for k in inputs:
			inp = inputs[k]
			inp.inp.stop()
			inp.inp.join()


if __name__ == '__main__':
	statprof.start()
	try:
		run()
	finally:
		statprof.stop()
		statprof.display()
