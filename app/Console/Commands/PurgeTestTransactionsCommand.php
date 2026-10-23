<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeTestTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-test-transactions 
                            {--force : Forzar la ejecución sin confirmación interactiva}
                            {--keep-customers : Preservar las cuentas de usuario con rol customer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purga quirúrgica de tablas transaccionales y operativas de prueba antes del despliegue a producción.';

    /**
     * Tablas transaccionales y operativas que deben purgarse al salir a producción (Dominio B).
     * Las tablas maestras, catálogo, usuarios y configuraciones CMS (Dominio A) NUNCA se tocan.
     */
    protected array $transactionalTables = [
        // Ventas, carritos y márgenes
        'order_financial_margins',
        'order_items',
        'orders',
        'cart_items',
        'cart_recovery_tokens',
        'carts',

        // Logística y entregas
        'shipping_reconciliation_items',
        'shipping_reconciliations',
        'shipping_discrepancy_claims',
        'shipping_manifests',
        'shipment_packages',
        'shipments',
        'rma_resolutions',
        'rma_inspection_logs',
        'rma_requests',

        // Operaciones de almacén, kardex y turnos
        'stock_locks',
        'inventory_movements',
        'packaging_movements',
        'cycle_counts',
        'fulfillment_batches',
        'operational_tasks',
        'shift_logs',
        'warehouse_ramp_entries',
        'vendor_shipments',

        // CRM, atención y soporte
        'lead_interactions',
        'leads',
        'support_cases',
        'chatbot_conversations',
        'interdepartmental_messages',

        // Suscripciones recurrentes y pasarelas
        'dunning_retry_logs',
        'customer_subscriptions',
        'payout_batches',
        'three_way_matchings',
        'tax_ledger_entries',
        'fraud_risk_assessments',
        'vendor_bills',

        // Convenios y atribución de prueba
        'ally_clearing_settlements',
        'ally_lead_captures',
        'ally_coupons_dir_b',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  PURGA QUIRÚRGICA DE TRANSACCIONES PRE-PRODUCCIÓN (DOMINIO B)  ');
        $this->info('================================================================');

        $isProduction = app()->environment('production');

        if ($isProduction && !$this->option('force')) {
            $this->error('ALERTA DE SEGURIDAD: Estás en entorno de PRODUCCIÓN.');
            $confirmation = $this->ask('Escribe "CONFIRMAR_PURGA_PRODUCCION" para proceder');
            if ($confirmation !== 'CONFIRMAR_PURGA_PRODUCCION') {
                $this->warn('Operación cancelada por el usuario. No se alteró ningún registro.');
                return 1;
            }
        } elseif (!$this->option('force')) {
            if (!$this->confirm('¿Deseas purgar todas las órdenes, envíos, tareas y registros operativos de prueba?', false)) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $driver = DB::getDriverName();
        $this->info("Conexión de base de datos activa: [{$driver}]");

        // Deshabilitar constraints foráneos
        $this->disableForeignKeys($driver);

        $purgedCount = 0;
        $summary = [];

        foreach ($this->transactionalTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->truncateTable($driver, $table);
                $summary[] = ['table' => $table, 'records_purged' => $count, 'status' => 'PURGED'];
                $purgedCount += $count;
            } else {
                $summary[] = ['table' => $table, 'records_purged' => 0, 'status' => 'SKIPPED (No existe)'];
            }
        }

        // Restablecer reservas de inventario físico
        if (Schema::hasTable('inventories')) {
            Inventory::query()->update(['reserved' => 0]);
            $this->info('Reservas en tabla inventories normalizadas a 0.');
        }

        // Purgar clientes ficticios si aplica
        if (!$this->option('keep-customers') && Schema::hasTable('users')) {
            $customerCount = User::where('role', 'customer')->count();
            User::where('role', 'customer')->delete();
            $summary[] = ['table' => 'users (role=customer)', 'records_purged' => $customerCount, 'status' => 'PURGED'];
            $purgedCount += $customerCount;
        }

        // Habilitar constraints foráneos
        $this->enableForeignKeys($driver);

        $this->table(['Tabla', 'Registros Purgados', 'Estado'], $summary);
        $this->info("Purga concluida exitosamente. Total registros eliminados: {$purgedCount}");
        $this->info('Catálogo, configuración de vitrinas, páginas y usuarios administrativos intactos.');

        return 0;
    }

    protected function disableForeignKeys(string $driver): void
    {
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        }
    }

    protected function enableForeignKeys(string $driver): void
    {
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        }
    }

    protected function truncateTable(string $driver, string $table): void
    {
        if ($driver === 'sqlite') {
            DB::table($table)->delete();
            try {
                DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$table}'");
            } catch (\Throwable $e) {
                // Ignore if sqlite_sequence does not exist
            }
        } else {
            try {
                DB::table($table)->truncate();
            } catch (\Throwable $e) {
                DB::table($table)->delete();
            }
        }
    }
}
