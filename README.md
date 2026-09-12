# OMS — Plataforma Omnicanal de Gestión de Pedidos, Inventario y Facturación

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat-square&logo=vue.js&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)

OMS (Order Management System) es una plataforma integral diseñada para centralizar y automatizar el ciclo de vida de los pedidos, la facturación asíncrona y el control estricto de inventario en tiempo real. Construida para soportar operaciones de alta concurrencia, la plataforma garantiza la integridad de los datos logísticos y financieros mediante una arquitectura robusta basada en Domain-Driven Design (DDD).
## Objetivo

Proveer un núcleo operativo resiliente que evite la sobreventa de inventario en escenarios de alta demanda (como ventas flash), sincronice el estado de los pedidos al instante entre los operadores de almacén y los clientes finales, y automatice procesos administrativos críticos para eliminar cuellos de botella en la distribución.

## Caso de Uso Real: Distribución y Venta Omnicanal

Múltiples clientes y sucursales intentan adquirir las últimas unidades de un producto simultáneamente. El OMS resuelve los retos operativos de este escenario:

- Prevención de Sobreventa: Mediante bloqueos pesimistas en la base de datos, el sistema garantiza que si quedan 5 unidades físicas, el sexto intento de compra sea rechazado instantáneamente, protegiendo la reputación del negocio.

- Coordinación de Almacén en Vivo: En el momento en que un cliente completa su pago, la orden pasa a estado Paid y las pantallas del personal de almacén se actualizan en milisegundos (vía WebSockets), permitiendo iniciar el empaquetado sin recargar el sistema.

- Gestión de Stock Precisa: El sistema distingue entre inventario "reservado" (durante el intento de compra) e inventario "consumido" (cuando el almacén despacha el pedido), reflejando con exactitud la realidad física del almacén en todo momento.

- Facturación sin Fricción: Mientras el almacén prepara el envío, el sistema procesa la generación de la factura PDF en segundo plano (Workers/Queues), asegurando que el servidor web nunca se bloquee y la experiencia del cliente sea fluida.

## Stack técnico

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue 3 (Composition API) + Inertia.js + Tailwind CSS
- **Tiempo real:** Laravel Reverb (WebSockets) + Laravel Echo
- **Colas:** Laravel Queues (driver `database`)
- **PDF:** barryvdh/laravel-dompdf
- **Testing:** Pest 4 (`pest-plugin-laravel`, `pest-plugin-arch`)
- **Observabilidad:** Laravel Pulse
- **Base de datos:** MySQL

## Arquitectura

El proyecto sigue **Domain-Driven Design táctico**, organizado por Bounded Contexts dentro de `app/Domain/`:

## Estructura

```text
app/
├── Application/            # Capa de entrega: Controllers HTTP que hablan con Inertia
│   └── Http/Controllers/
├── Domain/
│   ├── Orders/              # Ciclo de vida de una orden (máquina de estados)
│   │   ├── Actions/          # CreateOrderAction, TransitionOrderStateAction
│   │   ├── Models/           # Order, OrderItem, OrderStatusHistory
│   │   ├── States/           # Patrón State: Pending → Paid → Shipped → Delivered / Cancelled
│   │   ├── Events/           # OrderStatusChanged (broadcast por WebSocket)
│   │   └── Exceptions/
│   ├── Inventory/            # Control de stock con bloqueo pesimista
│   │   ├── Actions/          # Reserve / Consume / Release InventoryAction
│   │   ├── Models/           # Product, InventoryLocation, InventoryReservation
│   │   └── Exceptions/
│   └── Billing/              # Facturación asíncrona
│       ├── Jobs/             # GenerateInvoiceJob
│       ├── Listeners/        # DispatchInvoiceGenerationOnPaid
│       └── Models/           # Invoice
```

**Reglas de dependencia entre dominios (verificadas por tests de arquitectura, no solo por convención):**

- `Inventory` nunca depende de `Orders`.
- `Orders` nunca depende de `Billing` — es `Billing` quien escucha eventos de `Orders`, nunca al revés.
- Nada dentro de `Domain/` depende de `Application/` (los controllers dependen del dominio, no el dominio de los controllers).

Estas reglas se hacen cumplir automáticamente con **`pestphp/pest-plugin-arch`** — ver `tests/Feature/ArchitectureTest.php`. Si alguien rompe una frontera del dominio, el test falla en CI antes de llegar a producción.

## Roles y permisos

El sistema distingue dos roles de usuario (`users.role`):

- **`customer`** (cliente): puede crear órdenes, pagarlas, verlas y cancelarlas.
- **`warehouse_staff`** (personal de almacén): ve **todas** las órdenes del sistema (no solo las propias), y es el único que puede marcar una orden como enviada o entregada.

Toda regla de autorización vive en `app/Policies/OrderPolicy.php` y se aplica vía `Gate::authorize()` en cada controller — el frontend oculta botones según el rol solo por UX, pero la seguridad real está en el backend: una petición directa (ej. con Postman) que viole la política recibe **403 Forbidden** sin importar lo que muestre la interfaz.

## Funcionalidades destacadas

### 1. Inventario a prueba de condiciones de carrera
`ReserveInventoryAction` usa `DB::transaction()` + `lockForUpdate()` (bloqueo pesimista a nivel de fila) para garantizar que **nunca se puede sobrevender stock**, incluso bajo peticiones concurrentes reales. Probado con:
- Un test de concurrencia que simula 10 compradores peleando por 5 unidades de stock (`InventoryConcurrencyTest`).
- Un "botón de pánico" en el frontend (`/inventory`) que dispara 50 peticiones de compra simultáneas contra el mismo lote, visualizando en vivo cuántas tienen éxito vs. cuántas se rechazan.

### 2. Ciclo de vida completo del inventario
El stock reservado no es lo mismo que el stock consumido:
- **Reservar** (`ReserveInventoryAction`): al crear una orden, aparta stock sin tocar el físico.
- **Consumir** (`ConsumeInventoryAction`): al **enviar** la orden, el stock reservado se convierte en salida real de almacén.
- **Liberar** (`ReleaseInventoryAction`): al **cancelar** la orden, el stock reservado vuelve a estar disponible sin haber tocado el físico.

### 3. Máquina de estados con patrón State
`Order` delega su comportamiento de transición a clases dedicadas (`PendingState`, `PaidState`, `ShippedState`, `DeliveredState`, `CancelledState`), cada una declarando explícitamente a qué estados puede transicionar. Toda transición pasa por `TransitionOrderStateAction`, que:
- Valida que la transición sea legal.
- Registra un historial auditable en `order_status_histories`.
- Dispara los efectos colaterales correctos sobre el inventario (consumir o liberar).
- Emite el evento `OrderStatusChanged`.

### 4. Procesamiento asíncrono y facturación real en PDF
Cuando una orden pasa a `paid`, el listener `DispatchInvoiceGenerationOnPaid` (que vive en el dominio `Billing`, no en `Orders`) despacha `GenerateInvoiceJob` a una cola dedicada (`invoices`). El job genera un **PDF real** con `barryvdh/laravel-dompdf`, lo guarda en `storage/app/invoices/`, y queda disponible para descarga desde `/invoices` sin bloquear la respuesta al usuario mientras se genera.

### 5. Tiempo real sin recargar la página
`OrderStatusChanged` implementa `ShouldBroadcast` y se transmite por Laravel Reverb al canal público `orders-dashboard`. El componente Vue `Orders/Dashboard.vue` escucha ese canal con Laravel Echo y actualiza la tabla de órdenes al instante.

### 6. Seguridad
`OrderPolicy` garantiza que cada acción (ver, pagar, enviar, entregar, cancelar) respete tanto la propiedad de la orden como el rol del usuario — ver la sección "Roles y permisos" arriba. Rutas críticas (`/purchase-attempt`, `/checkout`) tienen rate limiting dedicado para prevenir abuso.

### 7. Observabilidad
Laravel Pulse (`/pulse`) monitorea en vivo el throughput de las colas, jobs lentos y excepciones — sin depender de Redis, usando la misma base de datos MySQL del proyecto.

## Instalación y Configuración

**Requisitos previos:**
- PHP 8.2+
- Composer
- Node.js y npm
- MySQL u otro gestor de base de datos compatible

```bash
git clone https://github.com/IsaiasG3/omnichannel-oms-laravel
cd oms-platform

composer install
npm install

copy .env.example .env
php artisan key:generate
```

Configura en `.env`:

```env
DB_CONNECTION=mysql
DB_DATABASE=oms_platform

QUEUE_CONNECTION=database

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=oms-app-id
REVERB_APP_KEY=oms-app-key
REVERB_APP_SECRET=oms-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Crea la base de datos `oms_platform` en tu gestor MySQL (Laragon la crea fácil desde su interfaz), luego:

```bash
php artisan migrate --seed
```

Esto siembra:
- Un usuario **cliente**: `test@test.com` / `password`
- Un usuario de **almacén**: `almacen@test.com` / `password`
- 6 productos con inventario (incluyendo uno con stock alto y otro con stock muy bajo, para pruebas de volumen)
- 5 órdenes de ejemplo en distintos estados del ciclo de vida — creadas usando las mismas Actions de dominio que usaría un usuario real, no inserts directos a la BD.

### Corre la aplicación

Necesitas **4 terminales abiertas simultáneamente**:

```bash
# Terminal 1 — servidor web
php artisan serve

# Terminal 2 — servidor de WebSockets
php artisan reverb:start

# Terminal 3 — worker de colas (nota el orden: invoices primero)
php artisan queue:work --queue=invoices,default

# Terminal 4 — compilación de assets en modo desarrollo
npm run dev
```

Entra a `http://localhost:8000`, inicia sesión, y explora desde `/dashboard`.

## Testing

```bash
./vendor/bin/pest
```

Suites incluidas:

- `InventoryConcurrencyTest` — valida que el bloqueo pesimista impide sobrevender stock bajo intentos concurrentes.
- `InventoryLifecycleTest` — valida que el stock se consume al enviar y se libera al cancelar.
- `ArchitectureTest` — hace cumplir las fronteras de los dominios DDD automáticamente.

## Decisiones de diseño

- **Inertia.js sin API separada:** las Actions de dominio son la única fuente de lógica de negocio; los controllers son delgados y solo traducen HTTP ↔ dominio.
- **Reservar vs. consumir inventario:** modelar esto como dos pasos distintos refleja cómo funciona un almacén real — el stock "sale" hasta que efectivamente se despacha, no en el momento del pago.
- **Tests de arquitectura sobre disciplina manual:** un test de `pest-plugin-arch` de hecho detectó una violación real durante el desarrollo (una dependencia inversa de `Orders` hacia `Billing`) que se corrigió antes de llegar a producción.
- **Laravel Pulse sobre Horizon:** Horizon requiere Redis; Pulse ofrece observabilidad de colas equivalente para este alcance sin esa dependencia adicional en Windows.
- **Roles a nivel de Policy, no de middleware:** separar "quién es dueño de la orden" de "qué rol tiene" dentro de `OrderPolicy` permite reglas compuestas (ej. cancelar: dueño O almacén) sin duplicar lógica de autorización en cada controller.

## Posibles mejoras futuras

- Contenerización con Laravel Sail / Docker Compose para levantar el entorno con un solo comando.
- CI con GitHub Actions corriendo la suite de Pest en cada push.
- Panel exclusivo para personal de almacén con vista consolidada de todas las órdenes pendientes de envío.

## Licencia

MIT