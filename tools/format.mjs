import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import prettier from 'prettier';
import beautify from 'js-beautify';

const root = fileURLToPath(new URL('../', import.meta.url));
const checkOnly = process.argv.includes('--check');
const skippedDirectories = new Set(['.git', 'node_modules', 'storage']);
let needsFormatting = false;

function formatTemplate(source) {
    const blocks = [];
    const protectedSource = source.replace(/<\?(?:php|=)[\s\S]*?\?>/g, (block) => {
        const index = blocks.push(block.replace(/\r\n/g, '\n')) - 1;
        return block.startsWith('<?=')
            ? `PHP_EXPRESSION_${index}_END`
            : `<!-- PHP_BLOCK_${index}_END -->`;
    });
    const html = beautify.html(protectedSource, {
        indent_size: 4,
        wrap_line_length: 0,
        end_with_newline: true,
        preserve_newlines: false,
        max_preserve_newlines: 1,
    });

    return html.replace(
        /<!-- PHP_BLOCK_(\d+)_END -->|PHP_EXPRESSION_(\d+)_END/g,
        (placeholder, statement, expression, offset) => {
            const block = blocks[Number(statement ?? expression)];
            const lineStart = html.lastIndexOf('\n', offset - 1) + 1;
            const indentation = html.slice(lineStart, offset).match(/^ */)[0];
            const lines = block.split('\n');
            const nonempty = lines.slice(1).filter((line) => line.trim());
            const margin = nonempty.length
                ? Math.min(...nonempty.map((line) => line.match(/^ */)[0].length))
                : 0;
            return lines
                .map((line, index) => (index ? indentation + line.slice(margin) : line))
                .join('\n');
        },
    );
}

async function formatDirectory(directory) {
    for (const entry of await fs.readdir(directory, { withFileTypes: true })) {
        const filename = path.join(directory, entry.name);
        if (entry.isDirectory()) {
            if (!skippedDirectories.has(entry.name)) await formatDirectory(filename);
            continue;
        }

        const info = await prettier.getFileInfo(filename, {
            ignorePath: path.join(root, '.prettierignore'),
            plugins: ['@prettier/plugin-php'],
        });
        if (info.ignored || !info.inferredParser) continue;

        const source = await fs.readFile(filename, 'utf8');
        const config = await prettier.resolveConfig(filename);
        const relativePath = path.relative(root, filename);
        const isTemplate =
            relativePath.startsWith(`views${path.sep}`) ||
            (relativePath.startsWith(`modules${path.sep}`) && entry.name === 'view.php');
        const formatted = isTemplate
            ? formatTemplate(source)
            : await prettier.format(source, { ...config, filepath: filename });

        if (source === formatted) continue;
        needsFormatting = true;
        const relative = path.relative(root, filename);
        if (checkOnly) console.error(`Needs formatting: ${relative}`);
        else {
            await fs.writeFile(filename, formatted);
            console.log(`Formatted: ${relative}`);
        }
    }
}

await formatDirectory(root);
if (checkOnly && needsFormatting) process.exitCode = 1;
else console.log(checkOnly ? 'All source files are formatted.' : 'Formatting complete.');
