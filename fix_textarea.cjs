const fs = require('fs');
let content = fs.readFileSync('resources/views/portal/finance.blade.php', 'utf8');
if(!content.includes('textarea { resize: vertical !important; }')) {
    content = `@push('styles')\n<style>\ntextarea { resize: vertical !important; }\n</style>\n@endpush\n` + content;
    fs.writeFileSync('resources/views/portal/finance.blade.php', content, 'utf8');
}
console.log('Done');
