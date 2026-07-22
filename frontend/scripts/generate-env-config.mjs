import fs from 'node:fs';
import path from 'node:path';

const projectRoot = process.cwd();
const envPath = path.join(projectRoot, '.env');
const outputDir = path.join(projectRoot, 'src', 'app', 'config');
const outputPath = path.join(outputDir, 'env.config.ts');
const defaultApiUrl = 'http://localhost:8000/api';

function readApiUrl() {
  if (!fs.existsSync(envPath)) {
    return defaultApiUrl;
  }

  const envContents = fs.readFileSync(envPath, 'utf8');
  const lines = envContents.split(/\r?\n/);

  for (const rawLine of lines) {
    const line = rawLine.trim();

    if (line === '' || line.startsWith('#')) {
      continue;
    }

    const separatorIndex = line.indexOf('=');

    if (separatorIndex === -1) {
      continue;
    }

    const key = line.slice(0, separatorIndex).trim();
    const value = line.slice(separatorIndex + 1).trim();

    if (key === 'API_URL') {
      return value.replace(/^['"]|['"]$/g, '') || defaultApiUrl;
    }
  }

  return defaultApiUrl;
}

function writeEnvConfig(apiUrl) {
  fs.mkdirSync(outputDir, { recursive: true });

  const fileContents = `// Auto-generated from .env - do not edit manually
// Run: npm run env:dev (local) or npm run env:prod (production)
// Contains the Laravel API base URL used by NewsService and CategoryService.
export const env = {
  apiUrl: '${apiUrl}',
};
`;

  fs.writeFileSync(outputPath, fileContents, 'utf8');
}

const apiUrl = readApiUrl();
writeEnvConfig(apiUrl);

console.log(`Generated ${path.relative(projectRoot, outputPath)} from .env`);
