const fs = require('fs');
const file = 'docs/archivo/legacy-specs/PROJECT_RULES.html';
let content = fs.readFileSync(file, 'utf8');

const insertStr = `
  <div class="rule-item">
    <div class="rule-number">77</div>
    <div class="rule-content">
      <strong>Regla 6.77: Optimización Responsiva del Panel de Administración (Tablets y Móviles)</strong>
      El panel de administración (RevOps) debe estar completamente optimizado para dispositivos móviles y tablets. Las tablas, formularios de datos, sidebars y paneles de configuración deben adaptar su diseño (layout) de forma fluida. El administrador debe poder realizar el 100% de las funciones operativas, de curaduría y configuración desde un dispositivo móvil o tablet, sin pérdida de contexto.
    </div>
  </div>`;

// find rule 6.76 closing tag
const findStr = `    </div>\r\n  </div>\r\n</div>\r\n\r\n<div class="page-break"></div>`;
const findStr2 = `    </div>\n  </div>\n</div>\n\n<div class="page-break"></div>`;

if (content.includes(findStr)) {
    content = content.replace(findStr, `    </div>\n  </div>` + insertStr + `\n</div>\n\n<div class="page-break"></div>`);
    fs.writeFileSync(file, content);
    console.log("Success with CRLF");
} else if (content.includes(findStr2)) {
    content = content.replace(findStr2, `    </div>\n  </div>` + insertStr + `\n</div>\n\n<div class="page-break"></div>`);
    fs.writeFileSync(file, content);
    console.log("Success with LF");
} else {
    console.log("Could not find the target string.");
}
