const fs = require('fs');
const path = 'docs/modules/hub-4-inventario-catalogo.md';
let content = fs.readFileSync(path, 'utf8');

const todoRegex = /## 4\. \[TODO[\s\S]*?(?=\n## 5)/;

const newText = `## 4. Reglas de Negocio Específicas

1. **Criterio de Redondeo (Costo Promedio Ponderado - CPP):**
   - **Regla SSOT:** El sistema calcula y almacena obligatoriamente los costos con **4 decimales** de precisión (\`decimal(12,4)\`) en la base de datos para evitar micro-fugas contables en grandes volúmenes. El redondeo a 2 decimales se aplica *únicamente* en la capa de presentación (UI) del Dashboard.
2. **Tratamiento Contable de Mermas (MERMA_RAMPA):**
   - **Regla SSOT:** Cuando se declara una merma física en rampa, el sistema descuenta inmediatamente el inventario físico para evitar sobreventas. Sin embargo, el impacto financiero contra el proveedor es **Manual**. El sistema NO emite notas de crédito automáticamente; el equipo de Finanzas/Compras debe emitir y conciliar la deuda con el taller de forma independiente.`;

content = content.replace(todoRegex, newText);
fs.writeFileSync(path, content, 'utf8');
console.log("Updated hub-4 docs");

