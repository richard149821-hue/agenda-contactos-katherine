#!/usr/bin/env python3
"""Empaqueta únicamente código público para un contenedor PHP ya preparado.

El resultado se configura en AGENDA_SOURCE; no contiene datos ni credenciales.
La conexión MySQL se configura mediante variables referenciadas de Railway.
"""
import argparse
import base64
import gzip
import hashlib
import json
from pathlib import Path

parser = argparse.ArgumentParser()
parser.add_argument('--salida', required=True)
args = parser.parse_args()
root = Path(__file__).resolve().parent.parent
files = [root/name for name in ['index.php','login.php','registro.php','salir.php','contacto.php','eliminar.php','acciones.php','.htaccess']]
for folder in ['app','assets','deploy','health']:
    files.extend(p for p in (root/folder).rglob('*') if p.is_file() and p.suffix not in ['.b64','.sha256'])
files += [root/'config/config.example.php',root/'config/.htaccess',root/'scripts/inicializar.php',
          root/'scripts/.htaccess',root/'database/agenda_contactos.sql',root/'database/.htaccess']
data = {p.relative_to(root).as_posix():p.read_text(encoding='utf-8') for p in sorted(files)}
assert 'config/config.php' not in data
raw = json.dumps(data,ensure_ascii=False,separators=(',',':')).encode('utf-8')
packed = base64.b64encode(gzip.compress(raw,mtime=0)).decode('ascii')
destination = Path(args.salida).resolve()
destination.parent.mkdir(parents=True,exist_ok=True)
destination.write_text(packed,encoding='ascii')
print(json.dumps({'archivos':len(data),'bytes_json':len(raw),'bytes_payload':len(packed),'sha256_json':hashlib.sha256(raw).hexdigest()}))
