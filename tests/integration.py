#!/usr/bin/env python3
"""Pruebas HTTP con usuarios nuevos y contactos ficticios, sin modificar cuentas ajenas."""
import argparse
import json
import re
import time
import uuid
from pathlib import Path
from urllib.parse import urljoin

import requests
from bs4 import BeautifulSoup

parser = argparse.ArgumentParser()
parser.add_argument('--base-url', required=True)
parser.add_argument('--output', default='')
args = parser.parse_args()
base = args.base_url.rstrip('/') + '/'
results = []

def check(condition, name):
    results.append({'prueba': name, 'resultado': 'correcto' if condition else 'fallo'})
    print(('OK  ' if condition else 'FAIL') + ' ' + name, flush=True)
    if not condition:
        raise AssertionError(name)

def get(session, path, **kwargs):
    return session.get(urljoin(base, path), timeout=30, **kwargs)

def token(response):
    tag = BeautifulSoup(response.text, 'html.parser').select_one('input[name=csrf]')
    assert tag, 'No se encontró token CSRF en ' + response.url
    return tag['value']

def names(response):
    return [tag.get_text(strip=True) for tag in BeautifulSoup(response.text, 'html.parser').select('tr[data-contact-id] .contact-person strong')]

def ids(response):
    return [int(tag['data-contact-id']) for tag in BeautifulSoup(response.text, 'html.parser').select('tr[data-contact-id]')]

def register(session, name, email, password):
    form = get(session, 'registro.php')
    return session.post(urljoin(base, 'registro.php'), data={'csrf': token(form), 'nombre': name,
        'correo': email, 'password': password, 'confirmacion': password}, timeout=30)

def login(session, email, password):
    form = get(session, 'login.php')
    return session.post(urljoin(base, 'login.php'), data={'csrf': token(form), 'correo': email,
        'password': password}, timeout=30)

def contact(session, data, id=None):
    path = 'contacto.php' + ('?id=' + str(id) if id else '')
    form = get(session, path)
    return session.post(urljoin(base, path), data={'csrf': token(form), **data}, timeout=30)

def action(session, name, id=None, csrf=None):
    csrf = token(get(session, 'index.php')) if csrf is None else csrf
    return session.post(urljoin(base, 'acciones.php'), data={'csrf': csrf, 'accion': name,
        'id': id or ''}, timeout=30)

started = time.time()
suffix = uuid.uuid4().hex[:12]
password = 'PruebaAcademica2026!'
email_a = f'qa.a.{suffix}@example.test'
email_b = f'qa.b.{suffix}@example.test'
a, b = requests.Session(), requests.Session()
try:
    r = get(a, 'index.php')
    check(r.url.endswith('/login.php'), 'Acceso privado sin sesión redirige al inicio de sesión')
    cookie_headers = ' '.join(response.headers.get('Set-Cookie','') for response in [*r.history,r]).lower()
    check('httponly' in cookie_headers and 'samesite=lax' in cookie_headers, 'Cookie de sesión HttpOnly y SameSite')
    if base.startswith('https://'):
        check(any(cookie.secure for cookie in a.cookies if cookie.name == 'agenda_session'), 'Cookie Secure en HTTPS')
    r = a.post(urljoin(base,'registro.php'), data={'nombre':'Sin token'}, timeout=30)
    check(r.status_code == 403, 'Registro rechaza solicitudes sin token CSRF')
    r = register(a,'QA Usuario Uno',email_a,password)
    check(r.url.endswith('/login.php') and 'Tu cuenta está lista' in r.text, 'Registro válido crea la cuenta')
    r = register(a,'QA Duplicado',email_a,password)
    check('Ese correo ya está registrado' in r.text, 'Se rechazan correos duplicados')
    r = login(a,email_a,'Incorrecta2026!')
    check('no son correctos' in r.text and r.url.endswith('/login.php'), 'Credenciales incorrectas no inician sesión')
    r = login(a,email_a,password)
    check(r.url.endswith('/index.php') and 'QA Usuario Uno' in r.text, 'Inicio de sesión válido muestra el panel personal')
    cookie = a.cookies.get('agenda_session')
    check(bool(cookie), 'La sesión autenticada conserva una cookie')
    payload = {'nombre':'Ana Prueba','telefono':'0990000011','correo':'ana.prueba@example.test','ciudad':'Loja','id_categoria':'1','favorito':'1','notas':'Prueba automática'}
    r = contact(a,payload)
    check(names(r) == ['Ana Prueba'], 'Crear contacto con categoría y favorito')
    first_id = ids(r)[0]
    cats = BeautifulSoup(get(a,'contacto.php').text,'html.parser').select('#id_categoria option[value]')
    check([x.get_text(strip=True) for x in cats if x['value']] == ['Familia','Amigos','Trabajo','Universidad','Otro'], 'Las cinco categorías requeridas están disponibles')
    payload2 = {**payload,'nombre':'Zoe Prueba','correo':'zoe.prueba@example.test','ciudad':'Cuenca','id_categoria':'3','favorito':'0'}
    r = contact(a,payload2)
    second_id = [i for i in ids(r) if i != first_id][0]
    check(set(names(r)) == {'Ana Prueba','Zoe Prueba'}, 'La creación persiste registros en la base de datos')
    for field, term in [('nombre','Ana Prueba'),('correo','ana.prueba@'),('ciudad','Loja')]:
        r = get(a,'index.php',params={'campo':field,'q':term})
        check(names(r) == ['Ana Prueba'], 'Búsqueda por ' + field)
    r = get(a,'index.php',params={'q':'Cuenca','campo':'todos'})
    check(names(r) == ['Zoe Prueba'], 'Búsqueda general combina nombre correo y ciudad')
    for order, expected in [('nombre_asc',['Ana Prueba','Zoe Prueba']),('nombre_desc',['Zoe Prueba','Ana Prueba']),('ciudad_asc',['Zoe Prueba','Ana Prueba']),('ciudad_desc',['Ana Prueba','Zoe Prueba']),('fecha_desc',['Zoe Prueba','Ana Prueba']),('fecha_asc',['Ana Prueba','Zoe Prueba'])]:
        check(names(get(a,'index.php',params={'orden':order})) == expected, 'Ordenamiento ' + order)
    check(names(get(a,'index.php',params={'favoritos':'1'})) == ['Ana Prueba'], 'Filtro de favoritos')
    check(names(get(a,'index.php',params={'categoria':'3'})) == ['Zoe Prueba'], 'Filtro por categoría')
    check(names(get(a,'index.php',params={'categoria':'1','favoritos':'1','q':'Ana','campo':'nombre'})) == ['Ana Prueba'], 'Búsqueda categoría y favoritos funcionan combinados')
    r = action(a,'favorito',second_id)
    check(set(names(get(a,'index.php',params={'favoritos':'1'}))) == {'Ana Prueba','Zoe Prueba'}, 'Activar favorito desde el listado')
    action(a,'favorito',second_id)
    check(names(get(a,'index.php',params={'favoritos':'1'})) == ['Ana Prueba'], 'Desactivar favorito desde el listado')
    invalid = contact(a,{**payload,'nombre':'Inválido','correo':'sin-arroba','id_categoria':'999'})
    check('correo electrónico válido' in invalid.text and 'Selecciona una' in invalid.text and len(names(get(a,'index.php'))) == 2, 'Validación del servidor impide correo y categoría inválidos')
    r = contact(a,{**payload,'nombre':'Ana Actualizada','ciudad':'Zamora'},first_id)
    check('Ana Actualizada' in names(r) and 'Zamora' in r.text, 'Edición de un contacto propio')
    check(names(get(a,'index.php',params={'q':"' OR 1=1 --"})) == [], 'La búsqueda resiste entrada de inyección SQL')
    check(names(get(a,'index.php',params={'q':'%'})) == [], 'El porcentaje se busca como texto literal')
    check(len(names(get(a,'index.php',params={'orden':'id; DROP TABLE contactos'}))) == 2, 'El ordenamiento admite solo columnas de la lista permitida')
    register(b,'QA Usuario Dos',email_b,password)
    login(b,email_b,password)
    check(names(get(b,'index.php')) == [], 'Una segunda cuenta no ve los contactos de la primera')
    check(get(b,'contacto.php?id='+str(first_id)).status_code == 404, 'No se puede abrir la edición de un contacto ajeno cambiando la URL')
    check(get(b,'eliminar.php?id='+str(first_id)).status_code == 404, 'No se puede abrir la confirmación de borrado de un contacto ajeno')
    for name in ['favorito','eliminar']:
        check(action(b,name,first_id).status_code == 404, 'Acción ' + name + ' rechaza un contacto ajeno')
    forged = b.post(urljoin(base,'contacto.php?id='+str(first_id)),data={'csrf':token(get(b,'index.php')),**payload2},timeout=30)
    check(forged.status_code == 404 and 'Ana Actualizada' in names(get(a,'index.php')), 'El POST manipulado no modifica contactos ajenos')
    check(action(a,'eliminar',first_id,csrf='token-invalido').status_code == 403 and first_id in ids(get(a,'index.php')), 'El borrado exige un token CSRF válido')
    check(get(a,'eliminar.php?id='+str(first_id)).status_code == 200 and first_id in ids(get(a,'index.php')), 'La pantalla de confirmación no borra por GET')
    action(a,'eliminar',first_id)
    check(first_id not in ids(get(a,'index.php')), 'La confirmación POST elimina el contacto propio')
    xss = '<script>alert(1)</script>'
    r = contact(a,{**payload,'nombre':xss,'correo':'xss@example.test'})
    check(xss not in r.text and '&lt;script&gt;' in r.text, 'Se escapa HTML en la salida para impedir XSS almacenado')
    xss_id = [i for i in ids(r) if i != second_id][0]
    action(a,'eliminar',xss_id)
    action(b,'ejemplos')
    check(len(names(get(b,'index.php'))) == 8, 'Carga opcional de ocho contactos ficticios por cuenta')
    action(b,'ejemplos')
    check(len(names(get(b,'index.php'))) == 8, 'La carga de ejemplos no duplica contactos')
    for i in range(3):
        contact(a,{**payload,'nombre':f'Página Prueba {i}','correo':f'pagina{i}@example.test','favorito':'0'})
    # Completar once registros en la cuenta A para comprobar el cambio de página.
    for i in range(7):
        contact(a,{**payload,'nombre':f'Registro Prueba {i}','correo':f'registro{i}@example.test','favorito':'0'})
    page1=get(a,'index.php');page2=get(a,'index.php?pagina=2')
    check(len(ids(page1))==10 and len(ids(page2))==1 and not set(ids(page1))&set(ids(page2)), 'Paginación sin registros repetidos entre páginas')
    for path in ['config/config.php','database/agenda_contactos.sql','app/helpers.php','tests/integration.py']:
        # El paquete de hosting omite pruebas y documentos; 404 también evita exponerlos.
        check(get(a,path).status_code in (403,404), 'Acceso web bloqueado a '+path)
    r=a.post(urljoin(base,'salir.php'),data={'csrf':token(get(a,'index.php'))},timeout=30)
    check(r.url.endswith('/login.php') and get(a,'index.php').url.endswith('/login.php'), 'Cerrar sesión revoca el acceso a las páginas privadas')
finally:
    summary={'base_url':base,'fecha_utc':time.strftime('%Y-%m-%dT%H:%M:%SZ',time.gmtime()),'duracion_segundos':round(time.time()-started,2),'total':len(results),'correctas':sum(r['resultado']=='correcto' for r in results),'pruebas':results}
    if args.output: Path(args.output).write_text(json.dumps(summary,ensure_ascii=False,indent=2),encoding='utf-8')
    print(json.dumps({k:v for k,v in summary.items() if k!='pruebas'},ensure_ascii=False))
