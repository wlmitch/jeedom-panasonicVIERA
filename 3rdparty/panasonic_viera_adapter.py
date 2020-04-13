#!/usr/bin/env python
# -*- coding: utf-8 -*-

# This file is part of Jeedom.
#
# Jeedom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Jeedom is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
#

import argparse
import json
import logging
import io
import sys

try:
    import panasonic_viera
except ImportError:
    print(json.dumps(None))
    sys.exit(0)


class ArrayHandler(logging.Handler):
    """Simple array handler that put all log messages to a list
    """
    def __init__(self, dest):
        """Init a new array Handler
        """
        logging.Handler.__init__(self)
        self.dest = dest

    def emit(self, record):
        self.dest.append(dict(level=record.levelname, message=record.getMessage()))

# Init
logs = []
hdlr = ArrayHandler(logs)
panasonic_viera._LOGGER.setLevel(logging.DEBUG)
panasonic_viera._LOGGER.addHandler(hdlr)

result = dict({'status': 0})

# CREATE PARSER
parser = argparse.ArgumentParser(description="Adapter for panasonic-viera for jeedom")
subparsers = parser.add_subparsers()

# PIN
parser_pin = subparsers.add_parser('pin', help='Start PIN request')
parser_pin.set_defaults(action='pin')
parser_pin.add_argument("host", help="The hostname of the TV")

# SENDPIN
parser_sendpin = subparsers.add_parser('sendpin', help='Send PIN for encryt key')
parser_sendpin.set_defaults(action='sendpin')
parser_sendpin.add_argument("host", help="The hostname of the TV")
parser_sendpin.add_argument("value", help="The PIN value")

# SENDKEY
parser_sendkey = subparsers.add_parser('sendkey', help='Simple sendkey action')
parser_sendkey.set_defaults(action='sendkey')
parser_sendkey.add_argument("host", help="The hostname of the TV")
parser_sendkey.add_argument("app_id", help="Application identifier")
parser_sendkey.add_argument("encryption_key", help="Encryption key")
parser_sendkey.add_argument("command", help="The command's code")

# RENDER (GET)
parser_render = subparsers.add_parser('render', help='Execute a render command')
parser_render.set_defaults(action='render')
parser_render.add_argument("host", help="The hostname of the TV")
parser_render.add_argument("app_id", help="Application identifier")
parser_render.add_argument("encryption_key", help="Encryption key")
parser_render.add_argument("command", help="The name of the information to render")

# SET
parser_set = subparsers.add_parser('set', help='Set a value')
parser_set.set_defaults(action='set')
parser_set.add_argument("host", help="The hostname of the TV")
parser_set.add_argument("app_id", help="Application identifier")
parser_set.add_argument("encryption_key", help="Encryption key")
parser_set.add_argument("command", help="The name of the information to set")
parser_set.add_argument("value", help="The new value to set")

args = parser.parse_args()
if not hasattr(args, 'action'):
    parser.print_help()
    sys.exit(1)

# MAIN
try:
    if hasattr(args, 'host'):
        if hasattr(args, 'app_id') & hasattr(args, 'encryption_key'):
            rc = panasonic_viera.RemoteControl(host=args.host, app_id=args.app_id, encryption_key=args.encryption_key)
        else:
            rc = panasonic_viera.RemoteControl(host=args.host)
    else:
        raise Exception("host must be defined")

    if args.action == 'sendkey':
        rc.send_key(args.command)
        result['output'] = 'ok'
    elif args.action == 'render':
        if args.command == 'getVolume':
            result['output'] = rc.get_volume()
        if args.command == 'getMute':
            result['output'] = rc.get_mute()
    elif args.action == 'set':
        if args.command == 'setVolume':
            rc.set_volume(args.value)
        if args.command == 'setMute':
            rc.set_mute(args.value)
    elif args.action == 'pin':
        rc.request_pin_code()
        result['output'] = 'ok'
    elif args.action == 'sendpin':
        rc.authorize_pin_code(pincode=args.value)
        result['app_id'] = rc._app_id
        result['enc_key'] = rc._enc_key
    else:
        raise Exception("L'action " + args.action + " n'est pas disponible.")
except Exception as e:
    result['output'] = 'nok'
    result['status'] = 1
    result['error'] = str(e)
    result['error_code'] = getattr(e, "getCode", None)

logging.shutdown()
result['log'] = logs
print(json.dumps(result))
sys.exit(result['status'])
