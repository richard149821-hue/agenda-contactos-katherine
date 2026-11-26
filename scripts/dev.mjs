// Lanzador opcional. La aplicación funciona directamente con PHP y Apache.
import { spawn } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
const path = new URL('../.dev-runtime.json', import.meta.url);
const runtime = existsSync(path) ? JSON.parse(readFileSync(path, 'utf8')) : {};
const command = runtime.command || ['php'];
const args = process.argv.slice(2);
const arg = (key, fallback) => args.includes(key) ? args[args.indexOf(key) + 1] : fallback;
const port = arg('--port', process.env.PORT || '3000');
const host = arg('--host', '127.0.0.1');
if (!/^\d{1,5}$/.test(port) || Number(port) > 65535) throw new Error('Puerto no válido');
const child = spawn(command[0], [...command.slice(1), '-S', `${host}:${port}`, 'dev_router.php'], {
  cwd: new URL('..', import.meta.url), env: {...process.env, ...runtime.env}, stdio: 'inherit'
});
child.on('error', () => { console.error('PHP no está disponible. Instala PHP 8.1 o superior con mysqli y mbstring.'); process.exit(1); });
for (const signal of ['SIGINT','SIGTERM']) process.on(signal, () => child.kill(signal));
child.on('exit', code => process.exit(code ?? 0));
