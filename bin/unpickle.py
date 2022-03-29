#!/usr/bin/env python3

import pickle
import json
import sys
import base64
import select

def isBase64(s):
    try:
        return s == base64.b64encode(base64.b64decode(s)).decode('ascii')
    except Exception:
        return False

bblob = None
if (len(sys.argv) > 1) and isBase64(sys.argv[1]):
    bblob = base64.b64decode(sys.argv[1])
elif select.select([sys.stdin, ], [], [], 0.0)[0]:
    try:
        with open(0, 'rb') as f:
            bblob = f.read()
    except Exception as e:
        err_unknown(e)

if bblob != None:
    unpik = pickle.loads(bblob)
    jsout = json.dumps(unpik)
    print(jsout)

