/**
 * Copia o editor Jodit (es2021) de node_modules para public/vendor/jodit,
 * para o sistema não depender de CDN. Rodado no postinstall.
 *
 * @license AGPL-3.0
 */
import { copyFileSync, existsSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const raiz = join(dirname(fileURLToPath(import.meta.url)), '..');
const origem = join(raiz, 'node_modules', 'jodit', 'es2021');
const destino = join(raiz, 'public', 'vendor', 'jodit');

if (!existsSync(origem)) {
    console.warn('[vendor-jodit] node_modules/jodit/es2021 não encontrado; nada a copiar.');
    process.exit(0);
}

mkdirSync(destino, { recursive: true });
for (const arquivo of ['jodit.min.js', 'jodit.min.css']) {
    copyFileSync(join(origem, arquivo), join(destino, arquivo));
}
console.log('[vendor-jodit] Jodit copiado para public/vendor/jodit.');
