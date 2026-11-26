#!/usr/bin/env python3
"""Crea un ZIP para htdocs sin credenciales ni archivos internos de desarrollo."""
import argparse
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED

parser = argparse.ArgumentParser()
parser.add_argument('--salida', required=True)
args = parser.parse_args()
root = Path(__file__).resolve().parent.parent
files = [root / name for name in ['index.php','login.php','registro.php','salir.php','contacto.php','eliminar.php','acciones.php','.htaccess']]
for name in ['app','assets','health']:
    files.extend(p for p in (root/name).rglob('*') if p.is_file())
files.extend([root/'config/config.example.php',root/'config/.htaccess'])
destination = Path(args.salida).resolve()
destination.parent.mkdir(parents=True, exist_ok=True)
with ZipFile(destination,'w',ZIP_DEFLATED) as archive:
    for path in sorted(files): archive.write(path,path.relative_to(root).as_posix())
print(f'ZIP de hosting preparado: {destination.name} · {len(files)} archivos')
