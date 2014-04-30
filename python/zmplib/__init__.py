# 
#  Library files used in ZMProxy
#

from collections import namedtuple

# Prototype for caching proxy for zoneminder(or other streaming resources as well)
Source = namedtuple('Source', 
                ['id', 'type', 'name', 'host', 'port' ,'path', 'interval', 'queuesz', 'user', 'passwd'], 
                verbose = False)

