// ==========================================================================
// REVOPS PANEL - HUB 4 (INVENTARIO): interactividad real de catalogo
// Selecciona filas, exporta CSV, aplica ajustes masivos via el endpoint real
// de movimientos (portal.inventory.movements.store, ya soporta JSON) y
// muestra el Kardex reciente por SKU (datos reales, sin mocks).
// ==========================================================================
(function () {
  document.addEventListener('alpine:init', function () {
    Alpine.data('inventoryCatalog', function (config) {
      return {
        products: config.products || [],
        movementUrlTemplate: config.movementUrlTemplate || '',
        csrfToken: config.csrfToken || '',
        q: '',
        statusFilter: '',
        selected: [],
        kardexProduct: null,
        bulkModalOpen: false,
        bulkForm: { type: 'increase', quantity: 1, reason: '' },
        bulkSubmitting: false,

        get filteredProducts() {
          var term = this.q.toLowerCase();
          return this.products.filter(function (p) {
            var matchesTerm = term === '' || (p.sku + ' ' + p.name).toLowerCase().indexOf(term) !== -1;
            var matchesStatus = this.statusFilter === '' || p.status === this.statusFilter;
            return matchesTerm && matchesStatus;
          }, this);
        },

        isSelected: function (id) { return this.selected.indexOf(id) !== -1; },
        toggleSelect: function (id) {
          var idx = this.selected.indexOf(id);
          if (idx === -1) { this.selected.push(id); } else { this.selected.splice(idx, 1); }
        },
        toggleSelectAll: function () {
          var visibleIds = this.filteredProducts.map(function (p) { return p.inventory_id; });
          var allSelected = visibleIds.length > 0 && visibleIds.every(this.isSelected.bind(this));
          this.selected = allSelected ? [] : visibleIds;
        },
        clearSelection: function () { this.selected = []; },

        openKardex: function (product) { this.kardexProduct = product; },
        closeKardex: function () { this.kardexProduct = null; },

        exportCsv: function () {
          var rows = this.products.filter(function (p) { return this.isSelected(p.inventory_id); }, this);
          if (rows.length === 0) { rows = this.filteredProducts; }
          var header = ['SKU', 'Producto', 'Stock local', 'Stock proveedor', 'Ubicacion', 'Estado'];
          var lines = [header.join(',')];
          rows.forEach(function (p) {
            var line = [p.sku, '"' + p.name.replace(/"/g, '""') + '"', p.local, p.dropship, p.location || '', p.status];
            lines.push(line.join(','));
          });
          var blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
          var url = URL.createObjectURL(blob);
          var link = document.createElement('a');
          link.href = url;
          link.download = 'catalogo-inventario-' + Date.now() + '.csv';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);
          if (window.RevopsPanel) window.RevopsPanel.showToast('CSV exportado: ' + rows.length + ' referencias');
        },

        openBulkModal: function () {
          if (this.selected.length === 0) return;
          this.bulkForm = { type: 'increase', quantity: 1, reason: '' };
          this.bulkModalOpen = true;
        },
        closeBulkModal: function () { this.bulkModalOpen = false; },

        applyBulkMovement: function () {
          if (!this.bulkForm.quantity || !this.bulkForm.reason) return;
          this.bulkSubmitting = true;
          var self = this;
          var ids = this.selected.slice();
          var requests = ids.map(function (id) {
            var url = self.movementUrlTemplate.replace('__ID__', id);
            return fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': self.csrfToken
              },
              body: JSON.stringify(self.bulkForm)
            }).then(function (resp) { return { id: id, ok: resp.ok }; });
          });
          Promise.all(requests).then(function (results) {
            self.bulkSubmitting = false;
            self.bulkModalOpen = false;
            var okCount = results.filter(function (r) { return r.ok; }).length;
            var failCount = results.length - okCount;
            if (window.RevopsPanel) {
              window.RevopsPanel.showToast(
                'Ajuste aplicado a ' + okCount + ' SKU(s)' + (failCount ? ' - ' + failCount + ' con error' : '')
              );
            }
            window.setTimeout(function () { window.location.reload(); }, 900);
          });
        }
      };
    });
  });
})();
