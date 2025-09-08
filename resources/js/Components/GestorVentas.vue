<template>
  <div class="print min-h-screen  flex rounded-2xl border-0 flex-col justify-center ">
    <div class="relative w-full px-4 sm:px-0 pb-8">
      <div class="relative px-4 bg-white shadow-lg rounded-3xl sm:p-20 sm:pt-8">
        <h1 class="text-3xl font-bold mb-6 text-center">Gestor de Ventas</h1>

        <!-- Filtro por día, semana o mes -->
        <div class="flex flex-col md:flex-row items-center gap-4 mb-8">
          <div class="w-full gap-2 flex flex-col items-center">
            <label for="filter" class="font-bold">Filtrar por:</label>
            <div class="flex gap-2">
              <select class="ml-1 py-0 rounded" id="filter" v-model="selectedFilter">
                <option value="day">Día</option>
                <option value="week">Semana</option>
                <option value="month">Mes</option>
              </select>

              <input class="w-fit h-fit p-1 rounded" id="valueSelect" type="date" v-if="selectedFilter === 'day'" v-model="selectedDate" />
              <input class="w-fit h-fit p-1 rounded" id="valueSelect" type="week" v-if="selectedFilter === 'week'" v-model="selectedWeek" />
              <input class="w-fit h-fit p-1 rounded" id="valueSelect" type="month" v-if="selectedFilter === 'month'" v-model="selectedMonth" />
            </div>
            <div class="flex gap-2">
              <label for="sucursal" class="font-bold">Sucursal: </label>
              <select class="ml-1 py-0 rounded" id="sucursal" v-model="selectedSucursal">
                <option v-for="sucursal in props.sucursales" :key="sucursal.id" :value="sucursal.id">{{ sucursal.nombre }}</option>
              </select>
            </div>
            <div class="text-xs text-gray-600 text-center mt-1 no-print">
              Seleccione los filtros y haga clic en "Aplicar Filtro"
            </div>
            <div class="flex gap-1" >
              <button :disabled="!selectedSucursal" :class="[!selectedSucursal ? 'bg-gray-400 cursor-not-allowed' : 'bg-orange-500 hover:bg-orange-600']" class="no-print text-sm rounded-lg shadow-lg px-3 py-2 bg-orange-500 text-white hover:bg-orange-600" @click="fetchFilteredData">Aplicar Filtro</button>
              <button :disabled="!selectedSucursal" :class="[!selectedSucursal ? 'bg-gray-400 cursor-not-allowed' : 'bg-gray-500 hover:bg-gray-600']" class="no-print text-sm rounded-lg shadow-lg px-3 py-2 bg-gray-500 text-white hover:bg-gray-600" @click="resetFilters">Limpiar Filtro</button>
            </div>
          </div>
        </div>

        <!-- Mensaje de error -->
        <div v-if="error" class="text-red-500 font-bold mt-2">{{ error }}</div>

        <!-- Cantidad inicial y final -->
        <div class="mb-6 no-print" v-if="isToday && $page.props.user.roles[0] !== 'supervisor'">
          <label for="initialCash" class="block text-sm font-medium text-gray-700">Cantidad Inicial en Caja</label>
          <div class="flex space-x-2">
            <input
              type="number"
              id="initialCash"
              v-model="initialCash"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
              min="0"
              step="0.01"
              placeholder="Ingrese cantidad"
            />
            <button @click="handleSaveInitialCash"
              :class="[
                'px-4 py-2 rounded-md',
                savingInitialCash ? 'bg-gray-500 text-gray-300' : 'text-white hover:bg-orange-600 bg-orange-500',
                initialCashSaved ? 'cursor-not-allowed bg-gray-500 text-gray-300' : ''
              ]"
              :disabled="savingInitialCash || initialCashSaved">
              {{ savingInitialCash ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>

          <label for="finalCash" class="block text-sm font-medium text-gray-700 mt-4">Cantidad Final en Caja</label>
          <div class="flex space-x-2">
            <input
              type="number"
              id="finalCash"
              v-model="finalCash"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
              min="0"
              step="0.01"
              placeholder="Ingrese cantidad"
            />
            <button @click="handleSaveFinalCash"
              :class="[
                'px-4 py-2 rounded-md',
                savingFinalCash ? 'bg-gray-500 text-gray-300' : 'text-white hover:bg-orange-600 bg-orange-500',
                finalCashSaved ? 'cursor-not-allowed bg-gray-500 text-gray-300' : ''
              ]"
              :disabled="savingFinalCash || finalCashSaved">
              {{ savingFinalCash ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
          <button
            v-if="finalCashSaved && !savingFinalCash && !savingNewCorte"
            @click="handleNewCorte"
            :class="[
              'mt-4 w-full px-4 py-2 rounded-md text-white',
              savingNewCorte ? 'bg-gray-500' : 'hover:bg-green-600 bg-green-500'
            ]"
            :disabled="savingNewCorte">
            {{ savingNewCorte ? 'Abriendo nuevo corte...' : 'Abrir nuevo corte' }}
          </button>
        </div>

        <!-- Resumen financiero -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
          <h2 class="text-xl font-semibold mb-4">Resumen Financiero</h2>
          <div class="grid grid-cols-2 gap-4">
            <!-- Lista de cortes -->
            <div v-if="props.cortes && props.cortes?.length > 0" class="col-span-2 mb-4">
              <h3 class="text-lg font-semibold mb-2">Cortes del día</h3>
              <div class="grid grid-cols-3 gap-4">
                <div v-for="(corte, index) in props.cortes" :key="corte.id"
                     class="bg-white p-3 rounded shadow"
                     >
                  <h4 class="font-medium">Corte #{{ index + 1 }}</h4>
                  <p class="text-sm text-gray-600">Inicial: ${{ safeToFixed(corte.dinero_inicio || 0) }}</p>
                  <p class="text-sm text-gray-600">Final: ${{ safeToFixed(corte.dinero_final || 0) }}</p>
                  <p class="text-sm text-gray-600">Hora inicio: {{ formatDate(corte.created_at) }}</p>
                  <p class="text-sm text-gray-600">Hora fin: {{ formatDate(corte.updated_at) }}</p>
                </div>
                
              </div>
              <button class="no-print mt-2 text-sm rounded-lg shadow-lg px-3 py-2 bg-gray-500 text-white hover:bg-gray-600 mb-4" @click="resetCorteSelection">
                Restablecer Selección
              </button>
            </div>

            <!-- Botón para restablecer la selección de cortes -->

            <div v-if="selectedFilter === 'day'">
              <p class="text-sm text-gray-600">Dinero inicial:</p>
              <p class="font-medium">${{ safeToFixed(initialCash) }}</p>
            </div>
            <div v-if="selectedFilter === 'day'">
              <p class="text-sm text-gray-600">Dinero final:</p>
              <p class="font-medium">${{ safeToFixed(finalCash) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Efectivo:</p>
              <p class="font-medium">${{ safeToFixed(cashPayments) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Tarjetas:</p>
              <p class="font-medium">${{ cardPayments }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Efectivo Totales <small class="text-xs text-gray-500 capitalize">(eliminados y no visibles)</small>:</p>
              <p class="font-medium">${{ safeToFixed(cashPaymentsTotal) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Tarjetas Totales <small class="text-xs text-gray-500 capitalize">(eliminados y no visibles)</small>:</p>
              <p class="font-medium">${{ safeToFixed(cardPaymentsTotal) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total ventas (Efectivo + Tarjetas):</p>
              <p class="font-medium">${{ Number(cashPayments) + Number(cardPayments) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Gastos:</p>
              <p class="font-medium">${{ totalGastos }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total menos gastos:</p>
              <p class="font-medium">${{ Number(cashPayments) + Number(cardPayments) - Number(totalGastos) }}</p>
            </div>
          </div>
        </div>

        <!-- Ventas -->
        <div  class="mb-6">
            <div class="flex justify-between" >
              <h2 class="text-xl font-semibold mb-4">Ventas</h2>
              <div class="flex gap-2">
                <button 
                  @click="crearNuevaVenta" 
                  :disabled="!selectedSucursal"
                  :class="[
                    'no-print size-fit py-1 px-3 rounded-md text-white',
                    selectedSucursal
                      ? 'hover:bg-green-600 bg-green-500' 
                      : 'bg-gray-400 cursor-not-allowed'
                  ]"
                  :title="!selectedSucursal ? 'Seleccione una sucursal primero' : ''"
                >
                  + Nueva Venta
                </button>
                <button 
                  @click="refolearVentasNormales" 
                  :disabled="!selectedSucursal || isRefoleandoVentasNormales"
                  :class="[
                    'no-print size-fit py-1 px-3 rounded-md text-white',
                    selectedSucursal && !isRefoleandoVentasNormales
                      ? 'hover:bg-blue-600 bg-blue-500' 
                      : 'bg-gray-400 cursor-not-allowed'
                  ]"
                  :title="!selectedSucursal ? 'Seleccione una sucursal primero' : (isRefoleandoVentasNormales ? 'Refolio en progreso' : '')"
                  :aria-label="isRefoleandoVentasNormales ? 'Refolio en progreso' : ''"
                >
                  <span v-if="isRefoleandoVentasNormales">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  Refolio ventas normales
                </button>
                <button @click="imprimir" class="no-print size-fit py-1 px-2 rounded-md text-white hover:bg-purple-600 bg-purple-500">
                  Imprimir
                </button>
              </div>
            </div>

          <!-- Filtros de búsqueda para columnas -->
          <div class="mb-4 no-print">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-lg font-semibold mb-3">Filtros de Búsqueda</h3>
              
              
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Filtro ID Venta -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">ID Venta</label>
                  <input
                    type="text"
                    v-model="filtros.idVenta"
                    placeholder="Buscar por ID..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  />
                </div>

                <!-- Filtro Creado por -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Creado por</label>
                  <input
                    type="text"
                    v-model="filtros.creadoPor"
                    placeholder="Buscar por nombre..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  />
                </div>

                <!-- Filtro Método de Pago -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                  <select
                    v-model="filtros.metodoPago"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  >
                    <option value="">Todos</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                  </select>
                </div>

                <!-- Filtro Factura -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Factura</label>
                  <select
                    v-model="filtros.factura"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  >
                    <option value="">Todos</option>
                    <option value="true">Facturado</option>
                    <option value="false">No facturado</option>
                  </select>
                </div>

                <!-- Filtro Rango de Precio -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Rango de Precio</label>
                  <div class="flex gap-2">
                    <input
                      type="number"
                      v-model="filtros.precioMin"
                      placeholder="Min"
                      class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    />
                    <input
                      type="number"
                      v-model="filtros.precioMax"
                      placeholder="Max"
                      class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <!-- Filtro Producto -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                  <input
                    type="text"
                    v-model="filtros.producto"
                    placeholder="Buscar por producto..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  />
                </div>

                <!-- Filtro Solo con Folio -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Solo con Folio</label>
                  <select
                    v-model="filtros.soloConFolio"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  >
                    <option value="">Todas las ventas</option>
                    <option value="true">Solo con folio</option>
                    <option value="false">Sin folio</option>
                  </select>
                </div>

                <!-- Filtro Tarjeta con Factura -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tarjeta + Factura</label>
                  <select
                    v-model="filtros.tarjetaConFactura"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  >
                    <option value="">Todas las ventas</option>
                    <option value="true">Solo tarjeta + factura</option>
                  </select>
                </div>

                <!-- Botones de acción -->
                <div class="flex items-end">
                  <div class="flex gap-2 w-full">
                    <button
                      @click="aplicarFiltros"
                      class="flex-1 bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors"
                    >
                      Aplicar Filtros
                    </button>
                    <button
                      @click="limpiarFiltros"
                      class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors"
                    >
                      Limpiar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="ventasPorCorte?.length <= 0" class="text-center py-8">
            <div class="text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No hay datos disponibles</h3>
              <p class="text-gray-500">
                {{ hasAppliedFilters ? 'No se encontraron ventas con los filtros aplicados.' : 'No se han vendido productos en este período.' }}
              </p>
              <div v-if="hasAppliedFilters" class="mt-4">
                <button 
                  @click="limpiarFiltros" 
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                >
                  Limpiar Filtros
                </button>
              </div>
              <div v-else-if="!props?.sucursal_id" class="mt-4">
                <p class="text-sm text-gray-400">
                  Seleccione una sucursal para ver las ventas disponibles.
                </p>
              </div>
              <div v-else-if="!props?.ventas || props.ventas.length === 0" class="mt-4">
                <p class="text-sm text-gray-400">
                  No hay ventas registradas para la sucursal y período seleccionados.
                </p>
              </div>
              <div v-else class="mt-4">
                <p class="text-sm text-gray-400">
                  No se encontraron ventas que coincidan con los criterios de búsqueda.
                </p>
              </div>
            </div>
          </div>
          
          <div v-else>
            <div v-for="corte in ventasPorCorte" :key="corte.id" class="mb-8">
              <div class="bg-gray-100 p-4 rounded-lg mb-4">
                <h3 class="text-lg font-semibold">
                  Corte #{{ corte.id }}
                  <span class="text-sm text-gray-600">
                    ({{ formatDate(corte.created_at) }} - {{ formatDate(corte.updated_at) }})
                  </span>
                </h3>
                <div class="grid grid-cols-3 gap-4 mt-2">
                  <div>
                    <p class="text-sm text-gray-600">Efectivo:</p>
                    <p class="font-medium">${{ safeToFixed(calcularTotalesPorCorte(corte.ventas).efectivo) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Tarjetas:</p>
                    <p class="font-medium">${{ safeToFixed(calcularTotalesPorCorte(corte.ventas).tarjeta) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Total:</p>
                    <p class="font-medium">${{ safeToFixed(calcularTotalesPorCorte(corte.ventas).total) }}</p>
                  </div>
                </div>
              </div>

              <!-- Tabla de Ventas - Ordenadas cronológicamente por fecha de creación (created_at) -->
              <!-- El ID Venta del Día se asigna automáticamente según el orden cronológico -->
              <div class="overflow-x-auto h-screen overflow-auto ventasR">
                <table class="tabla min-w-full divide-y overflow-auto divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 w-full text-left text-xs font-medium text-gray-500 uppercase tracking-wider no-print">
                        Visible
                      </th>
                      <th v-for="tab in tabTitles" :key="tab" class="px-6 py-3 w-full text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ tab }}
                      </th>
                      <th class="px-6 py-3 w-full text-left text-xs font-medium text-gray-500 uppercase tracking-wider no-print">
                        Estado
                      </th>
                      <th class="px-6 py-3 w-full text-left text-xs font-medium text-gray-500 uppercase tracking-wider no-print">
                        Acciones
                      </th>
                    </tr>
                  </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr  v-if="corte.ventas.length === 0 ">
                        <td colspan="10" class="text-center text-gray-500 py-8">
                          <div class="flex flex-col items-center">
                            <svg class="h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900">No hay ventas en este corte</span>
                            <span class="text-xs text-gray-500">Este corte no registró ninguna venta</span>
                          </div>
                        </td>
                      </tr>
                      <template v-else >
                        <tr v-for="venta in corte.ventas" :key="venta.id" 
                            :class="[ 
                              'divide-y divide-gray-200',
                              venta.estado === 'editada' ? 'bg-yellow-100' : 
                              venta.estado === 'creada' ? 'bg-green-100' :
                              'odd:bg-white even:bg-gray-100',
                              venta.estado === 'eliminada' ? 'bg-red-100 opacity-50 p-0 no-print' : '',
                              !venta.visible ? 'no-print' : ''
                            ]">
                          <td :class="[venta.estado === 'eliminada' ? 'p-0 m-0' : 'px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center no-print']">
                            <span v-if="venta.estado === 'eliminada'">  
                              <span class="text-xs text-center text-gray-500 italic">
                                Eliminado
                              </span>
                            </span>
                            <div v-else class="flex flex-col items-center">
                              <input type="checkbox" v-model="venta.visible" class="w-5 h-5" @change="updateVisibleVentas(venta.id, venta.visible)">
                              {{ venta.visible ? 'Visible' : 'No visible' }} 
                            </div>
                          </td>
                          <td :class="[venta.estado === 'eliminada' ? 'p-0 m-0' : 'px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center']">
                            <div class="flex flex-col items-center">
                              <span v-if="venta.estado === 'eliminada'">
                                <span class="text-xs text-gray-500 italic">
                                  Eliminado
                                </span>
                              </span>
                              <span v-else>{{ venta.folio ? venta.folio : '-' }}</span>
                              
                            </div>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                            <span v-if="venta.estado === 'eliminada'">
                              <span class="text-xs text-gray-500 italic">
                                Eliminado
                              </span>
                            </span>
                            <span v-else>{{ venta.idVentaDia || '-' }}</span>
                          </td>
                          <!-- <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                            <span v-if="venta.estado === 'eliminada'">
                              <span class="text-xs text-gray-500 italic">
                                Eliminado
                              </span>
                            </span>
                            <span v-else>{{ venta.idVentaNormal || '-' }}</span>
                          </td> -->
                      <td class="px-6 py-4 whitespace-nowrap overflow-auto text-sm text-gray-500">
                        <span v-if="venta.estado === 'eliminada'" class="text-xs text-gray-500 italic">
                          Eliminado
                        </span>
                        <span v-else class="print">
                          {{ `${venta?.usuario?.name}
                              ${venta?.usuario?.apellido_p ? venta?.usuario?.apellido_p : ''}
                              ${venta?.usuario?.apellido_m ? venta?.usuario?.apellido_m : ''}` }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        <span v-if="venta.estado === 'eliminada'">
                          <span class="text-xs text-gray-500 italic">
                            Eliminado
                          </span>
                        </span>
                        <span v-else>{{ formatDate(venta.created_at) }}</span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        <div v-if="venta.estado !== 'eliminada'" class="flex flex-col space-y-1">
                          <div v-for="producto in venta.detalles" :key="producto.id" class="flex justify-between">
                            <span>
                              {{ producto.cantidad }} × {{ producto.producto?.nombre || 'Producto desconocido' }}
                            </span>
                            <span
                              :class="[
                                'font-semibold',
                                (!producto?.cantidadEditado || producto?.cantidadEditado === producto?.cantidad )
                                  ? 'text-gray-700'
                                  : 'text-red-700'
                              ]">
                              ${{ (producto.producto?.precio ?? 0) * (producto.cantidad ?? 0) }}
                            </span>

                          </div>
                        </div>
                        <div v-if="venta.estado === 'eliminada'" class="flex flex-col space-y-1">
                          <span class="text-xs text-gray-500 italic">
                            Eliminado
                          </span>
                        </div>
                      </td>
                      <td  class="px-6 py-4 text-sm text-gray-500 capitalize text-center">
                        <span v-if="venta.estado === 'eliminada'">
                          <span class="text-xs text-gray-500 italic">
                            Eliminado
                          </span>
                        </span>
                        <span v-else :class="[venta.metodo_pago == 'tarjeta' ? 'bg-yellow-500' : 'bg-green-500']" class="px-2 py-1 rounded-md text-white">
                          {{ venta.metodo_pago }}
                        </span>
                      </td>
                      <td class="px-2 w-fit py-4 text-sm text-gray-500 capitalize text-center ">
                        <span v-if="venta.estado === 'eliminada'">
                          <span class="text-xs text-gray-500 italic">
                            Eliminado
                          </span>
                        </span>
                        <span v-else :class="[venta.factura == true ? 'bg-blue-500' : 'bg-red-500']" class="px-2 py-1 rounded-md text-white whitespace-nowrap capitalize">
                          {{ venta.factura ? 'Facturado' : 'No facturado' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900 font-semibold text-right">
                        <span v-if="venta.estado === 'eliminada'">
                          <span class="text-xs text-gray-500 italic">
                            Eliminado
                          </span>
                        </span>
                        <span v-else>${{ venta.total }}</span>
                      </td>
                      <td class="no-print">
                        <div class="flex text-xs justify-center gap-2 text-gray-500 capitalize">
                          {{venta.estado}}
                        </div>
                      </td>
                      <!--acciones-->
                      <td class="no-print px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                          <button 
                            v-if="venta.estado !== 'eliminada' && venta.visible !== false"
                            @click="editVenta(venta.id)" 
                            class="text-white rounded-md px-2 py-1 bg-orange-500 hover:bg-orange-700"
                          >
                            Editar
                          </button>
                          <button 
                            v-if="venta.estado !== 'eliminada' && venta.visible !== false"
                            @click="deleteVenta(venta.id)" 
                            class="text-white rounded-md px-2 py-1 bg-red-500 hover:bg-red-700"
                          >
                            Eliminar
                          </button>
                          <div class="flex text-xs justify-center items-center gap-2 text-gray-500 capitalize">
                            <span v-if="venta.estado === 'eliminada'" class="text-xs text-gray-500 italic">
                              Eliminado
                            </span>
                            <span v-if="venta.visible === false" class="text-xs text-gray-500 italic">
                              No visible
                            </span>
                            <button 
                              v-if="venta.estado === 'eliminada'"
                              @click="restaurarVenta(venta.id)" 
                              class="text-white rounded-md px-2 py-1 bg-green-500 hover:bg-green-700 text-xs"
                            >
                              Restaurar
                            </button>
                          </div>
                        </div>
                      </td>
                    </tr>
                        </template>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Mensaje de carga -->
        <div v-if="isLoading" class="text-center text-gray-500">Cargando...</div>
      </div>
    </div>
  </div>

  <Entradas :registrosInventario="registrosInventario" />

  <Gastos  :gastos="gastos" />

  <!--enviar numero de cortes-->

  <Sobrantes 
    v-if="sobrantes && sobrantes.length > 0"
    :cantidadDeCortes="cantidadDeCortes" 
    :sobrantes="sobrantes" 
    :categoriasInventario="categoriasInventario" 
    :sobrantesInventario="sobrantesInventario" 
  />

  <ChartCorte
      :ventasProductos="ventasProductos"
      :inventario="inventario"
  />

  <!-- Modal para Editar/Crear Venta -->
  <div v-if="isEditing || isCreating" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg overflow-auto w-11/12 min-w-2xl min-h-2xl max-w-7xl max-h-[100vh]">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">{{ isCreating ? 'Crear Nueva Venta' : 'Editar Venta' }}</h2>
        <button @click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información de la Venta -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-700">Información de la Venta</h3>
          
          <!-- Fecha y Hora -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora de Venta</label>
            <input
              type="datetime-local"
              v-model="ventaForm.fechaHora"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>

          <!-- Usuario -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vendido por</label>
            <select
              v-model="ventaForm.usuario_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="" selected>Seleccionar usuario</option>
              <option v-for="usuario in usuariosDisponibles" :key="usuario.id" :value="usuario.id">
                {{ usuario.name }} {{ usuario.apellido_p || '' }} {{ usuario.apellido_m || '' }}
              </option>
            </select>
          </div>
          
          <!-- Campo de sucursal solo visible al editar -->
          <div v-if="isEditing" class="mb-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
            <select 
              v-model="ventaForm.sucursal_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option v-for="sucursal in props?.sucursales" :key="sucursal.id" :value="sucursal.id">
                {{ sucursal.nombre }}
              </option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Puede cambiar la sucursal de la venta si es necesario</p>
          </div>
          
          <!-- Método de Pago -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
            <select
              v-model="ventaForm.metodo_pago"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta">Tarjeta</option>
            </select>
          </div>

          <!-- Factura -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Factura</label>
            <select
              v-model="ventaForm.factura"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option :value="false">No facturado</option>
              <option :value="true">Facturado</option>
            </select>
          </div>

          <!-- Folio 
          <div v-if="ventaForm.factura">
            <label class="block text-sm font-medium text-gray-700 mb-1">Folio <small class="text-xs text-gray-500">Opcional</small></label>
            
            <input
              type="text"
              v-model="ventaForm.folio"
              placeholder="Número de folio"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          -->
        </div>

        <!-- Productos -->
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-700">Productos</h3>
            <button
              @click="agregarProducto"
              class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 text-sm"
            >
              + Agregar Producto
            </button>
          </div>

          <!-- Lista de Productos -->
          <div class="space-y-3 max-h-64 overflow-y-auto">
            <div v-for="(producto, index) in ventaForm.productos" :key="index" class="border border-gray-200 rounded-lg p-3">
              <div class="flex justify-between items-start mb-2">
                <span class="text-sm font-medium text-gray-700">Producto {{ index + 1 }}</span>
                <button
                  @click="eliminarProducto(index)"
                  class="text-red-500 hover:text-red-700 text-sm"
                >
                  × Eliminar
                </button>
              </div>
              
              <!-- Selección de Producto -->
              <div class="mb-2">
                <select
                  v-model="producto.producto_id"
                  @change="actualizarPrecio(index)"
                  class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-orange-500"
                >
                  <option value="">Seleccionar producto</option>
                  <option v-for="prod in inventario" :key="prod.id" :value="prod.id">
                    {{ prod.nombre }} ({{ prod.detalle }}) - ${{ prod.precio }}
                  </option>
                </select>
              </div>

              <!-- Cantidad y Precio -->
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-xs text-gray-600 mb-1">Cantidad</label>
                  <input
                    type="number"
                    v-model="producto.cantidad"
                    @input="calcularSubtotal(index)"
                    min="1"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-orange-500"
                  />
                </div>
                <div>
                  <label class="block text-xs text-gray-600 mb-1">Precio Unit.</label>
                  <input
                    type="number"
                    disabled
                    v-model="producto.precio_unitario"
                    @input="calcularSubtotal(index)"
                    step="0.01"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                  />
                </div>
              </div>

              <!-- Subtotal -->
              <div class="text-right">
                <span class="text-sm text-gray-600">Subtotal: ${{ producto.subtotal || 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Total -->
          <div class="border-t pt-4">
            <div class="text-right">
              <span class="text-lg font-bold">Total: ${{ totalVenta }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Botones de Acción -->
      <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
        <button
          @click="cancelEdit"
          class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
        >
          Cancelar
        </button>
        <button
          @click="saveEditedVenta"
          class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
        >
          {{ isCreating ? 'Crear Venta' : 'Guardar Cambios' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { route } from '../../../vendor/tightenco/ziggy/src/js'
import ChartCorte from './ChartCorte.vue'
import Entradas from './Entradas.vue'
import Gastos from './Gastos.vue'
import Sobrantes from './Sobrantes.vue'

const { props } = usePage()

const sobrantes = ref(props.sobrantes || [])
const sobrantesInventario = ref(props.sobrantesInventario || [])
const cantidadDeCortes = ref(props?.cantidadDeCortes || 0)
const categoriasInventario = ref(props?.categoriasInventario || [])
const selectedFilter = ref('day')
const today = new Date();
const selectedDate = ref(today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0'))
const selectedWeek = ref(null)
const selectedMonth = ref(null)
const initialCash = ref(props?.corte?.dinero_inicio || 0)
const finalCash = ref(props?.corte?.dinero_final || 0)
const cashPayments = ref(0)
const cardPayments = ref(0)
const productsUsed = ref(props.productosVendidos || [])
const isLoading = ref(false)
const error = ref('')
const ventasProductos = ref(props.ventasProductos || [])
const inventario = ref(props.inventario || [])
// Función helper para normalizar el campo visible de las ventas
const normalizarVentas = (ventasArray) => {
  if (!Array.isArray(ventasArray)) return [];
  return ventasArray.map(venta => ({
    ...venta,
    visible: venta.visible === true || venta.visible === 1 || venta.visible === '1'
  }));
};

const ventas = ref(normalizarVentas(props.ventas || []))
const gastos = ref(props.gastos || [])
const totalGastos = ref(props.totalGastos || 0)
const cantidadCortes = ref(props?.cantidadCortes || 0)
const sucursales = ref(props?.sucursales || [])
const selectedSucursal = ref(null)
const cortes = ref(props?.cortes || [])


// Estados para controlar el guardado de cantidades
const savingInitialCash = ref(false)
const savingFinalCash = ref(false)
const initialCashSaved = ref(!!props?.corte?.dinero_inicio)
const finalCashSaved = ref(!!props?.corte?.dinero_final)
const savingNewCorte = ref(false)

// total de ventas totales en efectivo y con tarjeta
const cashPaymentsTotal = ref(props?.cashPaymentsTotal || 0)
const cardPaymentsTotal = ref(props?.cardPaymentsTotal || 0)

const tabTitles = ['Folio', 'ID Venta', 'Creado por', 'Hora', 'Productos vendidos', 'Metodo de pago', 'Factura', 'Total']

const isToday = computed(() => {
  const today = new Date();
  const formattedToday = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
  return selectedFilter.value === 'day' && selectedDate.value === formattedToday;
});

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '-';
  return date.toLocaleString('es-MX', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
    timeZone: 'America/Mexico_City'
  });
}

// Función para convertir fecha del backend al formato datetime-local sin conversión de zona horaria
const convertirFechaBackend = (fechaBackend) => {
  if (!fechaBackend) return '';
  
  // Si la fecha viene como string "2025-08-28 21:03:00"
  if (typeof fechaBackend === 'string') {
    // Reemplazar el espacio por 'T' para crear un formato ISO válido
    const fechaISO = fechaBackend.replace(' ', 'T');
    const fecha = new Date(fechaISO);
    
    if (isNaN(fecha.getTime())) return '';
    
    // Extraer componentes de la fecha sin conversión de zona horaria
    const year = fecha.getFullYear();
    const month = String(fecha.getMonth() + 1).padStart(2, '0');
    const day = String(fecha.getDate()).padStart(2, '0');
    const hours = String(fecha.getHours()).padStart(2, '0');
    const minutes = String(fecha.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }
  
  // Si ya es un objeto Date
  if (fechaBackend instanceof Date) {
    const year = fechaBackend.getFullYear();
    const month = String(fechaBackend.getMonth() + 1).padStart(2, '0');
    const day = String(fechaBackend.getDate()).padStart(2, '0');
    const hours = String(fechaBackend.getHours()).padStart(2, '0');
    const minutes = String(fechaBackend.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }
  
  return '';
};

const ventasPorCorte = ref([])

// Variable para detectar si se han aplicado filtros
const hasAppliedFilters = computed(() => {
  return Object.values(filtros.value).some(valor => {
    if (typeof valor === 'string') {
      return valor.trim() !== '';
    }
    if (typeof valor === 'number') {
      return valor !== null && valor !== undefined;
    }
    return valor !== null && valor !== undefined;
  });
});

//separar ventar por cada corte
const SepararVentaPorCorte = () => {
  ventasPorCorte.value = []
  
  // Si no hay ventas, mostrar mensaje
  if (!ventas.value || ventas.value?.length === 0) {
    return
  }
  
  // Si no hay cortes, mostrar todas las ventas en un solo grupo
  if (!cortes.value || cortes.value?.length === 0) {
    ventasPorCorte.value = [{
      id: 1,
      ventas: ventas.value,
      created_at: null,
      updated_at: null
    }]
    return
  }

  // Ordenar los cortes por fecha de creación
  const cortesOrdenados = [...cortes.value].sort((a, b) => 
    new Date(a.created_at) - new Date(b.created_at)
  )

  // Procesar cada corte
  cortesOrdenados.forEach((corte, index) => {
    const corteFin = corte.updated_at ? new Date(corte.updated_at) : null
    const corteAnterior = index > 0 ? cortesOrdenados[index - 1] : null
    const corteAnteriorFin = corteAnterior?.updated_at ? new Date(corteAnterior.updated_at) : null

    let ventasDelCorte = []

    if (index === 0) {
      // Primer corte: incluir todas las ventas desde el inicio del día hasta el final del primer corte
      ventasDelCorte = ventas.value.filter(venta => {
        const ventaDate = new Date(venta.created_at)
        return ventaDate <= (corteFin || new Date())
      })
    } else if (corteFin) {
      // Corte intermedio con hora de finalización: incluir ventas desde el final del corte anterior hasta el final de este corte
      ventasDelCorte = ventas.value.filter(venta => {
        const ventaDate = new Date(venta.created_at)
        return ventaDate > corteAnteriorFin && ventaDate <= corteFin
      })
    } else {
      // Último corte sin hora de finalización: incluir todas las ventas después del final del corte anterior
      ventasDelCorte = ventas.value.filter(venta => {
        const ventaDate = new Date(venta.created_at)
        return ventaDate > corteAnteriorFin
      })
    }

    ventasPorCorte.value.push({
      id: corte.id,
      ventas: ventasDelCorte,
      created_at: corte.created_at,
      updated_at: corte.updated_at,
      dinero_inicio: corte.dinero_inicio,
      dinero_final: corte.dinero_final
    })
  })
}

// Calcular totales por corte (excluyendo ventas eliminadas y no visibles)
const calcularTotalesPorCorte = (ventas) => {
  // Filtrar ventas válidas para el cálculo
  const ventasValidas = ventas.filter(venta => 
    venta.estado !== 'eliminada' && venta.visible === true
  );
  
  return {
    efectivo: ventasValidas.reduce((total, venta) => 
      venta.metodo_pago === 'efectivo' ? total + Number(venta.total) : total, 0),
    tarjeta: ventasValidas.reduce((total, venta) => 
      venta.metodo_pago === 'tarjeta' ? total + Number(venta.total) : total, 0),
    total: ventasValidas.reduce((total, venta) => total + Number(venta.total), 0)
  }
}

const registrosInventario = ref(props?.registrosInventario || [])

const  fetchFilteredData = () => {
  isLoading.value = true;
  error.value = '';

  let filter = selectedFilter.value;
  let value = null;

  if (filter === 'day') {
    value = selectedDate.value;
  } else if (filter === 'week') {
    value = selectedWeek.value;
  } else if (filter === 'month') {
    value = selectedMonth.value;
  }

  router.post('/gestor-ventas/filtro', {
    filter: filter,
    value: value,
    sucursal_id: selectedSucursal.value
  }, {
    preserveScroll: true,
    onSuccess(response) {
      try {
        // Validar que response.props existe
        if (!response || !response.props) {
          console.error('Respuesta inválida del servidor:', response);
          error.value = 'Error: Respuesta inválida del servidor';
          isLoading.value = false;
          return;
        }
        ventasPorCorte.value = []
        // Actualizar valores con validación
        cashPayments.value = response.props.cashPayments || 0;
        cardPayments.value = response.props.cardPayments || 0;
        productsUsed.value = Array.isArray(response.props.productsUsed) ? response.props.productsUsed : [];
        ventas.value = normalizarVentas(response.props.ventas || []);
        initialCash.value = response.props.initialCash || 0;
        finalCash.value = response.props.finalCash || 0;

        ventasProductos.value = Array.isArray(response.props.ventasProductos) ? response.props.ventasProductos : [];
        inventario.value = Array.isArray(response.props.inventario) ? response.props.inventario : [];
        registrosInventario.value = Array.isArray(response.props.registrosInventario) ? response.props.registrosInventario : [];
        sucursales.value = Array.isArray(response.props.sucursales) ? response.props.sucursales : [];
        gastos.value = Array.isArray(response.props.gastos) ? response.props.gastos : [];
        totalGastos.value = response.props.totalGastos || 0;
        cortes.value = Array.isArray(response.props.cortes) ? response.props.cortes : [];
        cantidadCortes.value = response.props.cantidadCortes || 0;
        sobrantes.value = Array.isArray(response.props.sobrantes) ? response.props.sobrantes : [];
        sobrantesInventario.value = Array.isArray(response.props.sobrantesInventario) ? response.props.sobrantesInventario : [];
        cantidadDeCortes.value = response.props.cantidadDeCortes || 0;
        categoriasInventario.value = Array.isArray(response.props.categoriasInventario) ? response.props.categoriasInventario : [];
        usuariosDisponibles.value = Array.isArray(response.props.users) ? response.props.users : [];

        cashPaymentsTotal.value = response.props.cashPaymentsTotal || 0;
        cardPaymentsTotal.value = response.props.cardPaymentsTotal || 0;

        showToast("success", "Filtro actualizado correctamente");
        
        // Solo llamar a estas funciones si hay datos válidos
        if (ventas.value && ventas.value.length > 0) {
          // calculatePayments() ya no es necesario porque los datos vienen del backend
        }
        
        if (cortes.value && cortes.value.length > 0 && ventas.value && ventas.value.length > 0) {
          SepararVentaPorCorte(); // Llamar a SepararVentaPorCorte después de actualizar los datos
        } else if (ventas.value && ventas.value.length > 0) {
          // Si no hay cortes pero sí ventas, crear un grupo por defecto
          ventasPorCorte.value = [{
            id: 1,
            ventas: ventas.value,
            created_at: null,
            updated_at: null
          }];
          
        }
        
        // Actualizar datos originales con los nuevos datos filtrados
        // Las ventas ya vienen ordenadas cronológicamente por created_at desde el backend
        ventasPorCorteOriginal.value = JSON.parse(JSON.stringify(ventasPorCorte.value));
        
        isLoading.value = false;
      } catch (error) {
        console.error('Error al procesar la respuesta:', error);
        error.value = 'Error al procesar los datos recibidos';
        isLoading.value = false;
      }
    },
    onError(e) {
      console.error('Error en la petición:', e);
      showToast("error", e.props?.flash?.error || "Error al obtener datos con este filtro");
      error.value = 'Ocurrió un error al obtener los datos. Inténtalo de nuevo.';
      isLoading.value = false;
    }
  });
}

const resetFilters = () => {
  selectedFilter.value = 'day'
  selectedDate.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
  selectedWeek.value = null
  selectedMonth.value = null
  selectedSucursal.value = null
  selectedCorteId.value = null // Resetear también la selección de corte
    
  // Limpiar también los filtros de búsqueda
  limpiarFiltros();
  
  showToast("info", "Filtros reseteados. Haga clic en 'Aplicar Filtro' para ver los datos.");

}

const imprimir = () => {
  window.print()
}

const selectedCorteId = ref(null);

// Variables para filtros de búsqueda
const filtros = ref({
  idVenta: '',
  creadoPor: '',
  metodoPago: '',
  factura: '',
  precioMin: '',
  precioMax: '',
  producto: '',
  soloConFolio: '',
  tarjetaConFactura: ''
});

// Variable para almacenar los datos originales
const ventasPorCorteOriginal = ref([]);

// Función para aplicar filtros
const aplicarFiltros = () => {
  // Si no hay datos originales, usar los actuales
  if (!ventasPorCorteOriginal.value || ventasPorCorteOriginal.value.length === 0) {
    ventasPorCorteOriginal.value = JSON.parse(JSON.stringify(ventasPorCorte.value));
  }
  
  // Si no hay datos para filtrar, mostrar mensaje y salir
  if (!ventasPorCorteOriginal.value || ventasPorCorteOriginal.value.length === 0) {
    showToast("warning", "No hay datos disponibles para aplicar filtros.");
    return;
  }

  // Aplicar filtros sobre los datos originales
  const datosFiltrados = ventasPorCorteOriginal.value.map(corte => {
    const ventasFiltradas = corte.ventas.filter(venta => {
      // Filtro por ID de venta
      if (filtros.value.idVenta && !venta.idVentaDia?.toString().includes(filtros.value.idVenta) && 
          !venta.id?.toString().includes(filtros.value.idVenta)) {
        return false;
      }

      // Filtro por creador
      if (filtros.value.creadoPor) {
        const nombreCompleto = `${venta?.usuario?.name || ''} ${venta?.usuario?.apellido_p || ''} ${venta?.usuario?.apellido_m || ''}`.toLowerCase();
        if (!nombreCompleto.includes(filtros.value.creadoPor.toLowerCase())) {
          return false;
        }
      }

      // Filtro por método de pago
      if (filtros.value.metodoPago && venta.metodo_pago !== filtros.value.metodoPago) {
        return false;
      }

      // Filtro por factura
      if (filtros.value.factura !== '') {
        const facturaBoolean = filtros.value.factura === 'true';
        if (Boolean(venta.factura) !== facturaBoolean) {
          return false;
        }
      }

      // Filtro por rango de precio
      if (filtros.value.precioMin && Number(venta.total) < Number(filtros.value.precioMin)) {
        return false;
      }
      if (filtros.value.precioMax && Number(venta.total) > Number(filtros.value.precioMax)) {
        return false;
      }

      // Filtro por producto
      if (filtros.value.producto) {
        const tieneProducto = venta.detalles?.some(detalle => 
          detalle.producto?.nombre?.toLowerCase().includes(filtros.value.producto.toLowerCase())
        );
        if (!tieneProducto) {
          return false;
        }
      }

      // Filtro solo con folio
      if (filtros.value.soloConFolio === 'true' && !venta.folio) {
        return false;
      }
      if (filtros.value.soloConFolio === 'false' && venta.folio) {
        return false;
      }

      // Filtro tarjeta con factura
      if (filtros.value.tarjetaConFactura === 'true') {
        if (venta.metodo_pago !== 'tarjeta' || !venta.factura) {
          return false;
        }
      }

      return true;
    });

    return {
      ...corte,
      ventas: ventasFiltradas
    };
  });

  // Filtrar cortes que no tengan ventas después del filtrado
  ventasPorCorte.value = datosFiltrados.filter(corte => corte.ventas.length > 0);
  
  // Debug: mostrar información sobre los datos filtrados
  console.log('Datos originales:', ventasPorCorteOriginal.value);
  console.log('Datos filtrados:', datosFiltrados);
  console.log('Cortes con ventas:', ventasPorCorte.value);
  
  // Mostrar mensaje informativo si no hay resultados
  if (ventasPorCorte.value.length === 0) {
    showToast("info", "No se encontraron ventas con los filtros aplicados. Intente ajustar los criterios de búsqueda.");
    // Asegurar que la tabla se actualice para mostrar "No hay datos disponibles"
    ventasPorCorte.value = [];
  } else {
    showToast("success", `Se encontraron ${ventasPorCorte.value.length} corte(s) con ventas que coinciden con los filtros.`);
  }
};

// Función para limpiar filtros
const limpiarFiltros = () => {
  filtros.value = {
    idVenta: '',
    creadoPor: '',
    metodoPago: '',
    factura: '',
    precioMin: '',
    precioMax: '',
    producto: '',
    soloConFolio: '',
    tarjetaConFactura: ''
  };
  
  // Restaurar datos originales
  if (ventasPorCorteOriginal.value && ventasPorCorteOriginal.value.length > 0) {
    // Si hay datos originales, restaurarlos
    ventasPorCorte.value = JSON.parse(JSON.stringify(ventasPorCorteOriginal.value));
  } else {
    // Si no hay datos originales, recargar desde props
    if (props.ventas && props.ventas.length > 0) {
              ventas.value = normalizarVentas([...props.ventas]);
      SepararVentaPorCorte();
    } else {
      ventasPorCorte.value = [];
    }
  }
  
  showToast("success", "Filtros limpiados correctamente");
};

const filterVentasByCorte = (corteId, createdAt, updatedAt) => {
  // Validar que corteId, createdAt y updatedAt sean válidos
  if (!corteId || !createdAt || !updatedAt) {
    console.error("Parámetros inválidos:", { corteId, createdAt, updatedAt });
    return;
  }

  selectedCorteId.value = corteId;

  // Filtrar las ventas que están dentro del rango de tiempo del corte
  ventas.value = normalizarVentas(props.ventas.filter(venta => {
    // Verifica que venta.created_at sea una cadena de fecha válida
    if (!venta.created_at) {
      return false;
    }

    const ventaDate = new Date(venta.created_at);
    if (isNaN(ventaDate)) {
      return false;
    }

    return ventaDate >= new Date(createdAt) && ventaDate <= new Date(updatedAt);
  }));

  // Actualizar los pagos en efectivo y con tarjeta
  // calculatePayments() ya no es necesario porque los datos vienen del backend
};

const resetCorteSelection = () => {
  selectedCorteId.value = null;
  ventas.value = normalizarVentas(props.ventas); // Mostrar todas las ventas
  // calculatePayments() ya no es necesario porque los datos vienen del backend
};

const showToast = (icon, title) => {
  Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    customClass: "no-print",
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire({
    icon,
    title
  })
}

const handleSaveInitialCash = () => {
  const data = {
    sucursal_id: props?.sucursal_id,
    usuario_id: props?.usuario_id,
    fecha: selectedDate.value,
    dinero_inicio: initialCash.value
  };

  savingInitialCash.value = true;

  try {
    router.post(route('corte-caja.guardar-inicial'), data, {
      preserveScroll: true,
      onSuccess: (e) => {
        if(e.props.flash.success){
          if (e.props.corte) {
            initialCash.value = e.props.corte.dinero_inicio;
            initialCashSaved.value = true;
          }
          showToast("success", e.props.flash.success);
        }else{
          showToast("error", e.props.flash.error);
        }
        savingInitialCash.value = false;
      }
    });
  } catch (e) {
    showToast("error", e.error || "Error al guardar la cantidad inicial");
    savingInitialCash.value = false;
  }
};

const handleSaveFinalCash = () => {
  const data = {
    sucursal_id: props?.sucursal_id,
    usuario_id: props?.usuario_id,
    fecha: selectedDate.value,
    dinero_final: finalCash.value
  };

  savingFinalCash.value = true;

  try {
    router.post('/corte-caja/guardar-final', data, { preserveScroll: true,
      onSuccess: (e) => {
        if(e.props.flash.success){
          // Actualizar el corte en los datos locales
          if (e.props.corte) {
            finalCash.value = e.props.corte.dinero_final;
            finalCashSaved.value = true;
          }
          showToast("success", e.props.flash.success);
        }else{
          showToast("error", e.props.flash.error);
        }
        savingFinalCash.value = false;
      },
    })
  } catch (e) {
    showToast("error", e.error || "Error al guardar la cantidad final");
    savingFinalCash.value = false;
  }
};

onMounted(() => {
  // Asegurarse de que los datos estén inicializados
  if (props.ventas && Array.isArray(props.ventas)) {
    ventas.value = normalizarVentas(props.ventas)
  }
  
  SepararVentaPorCorte()
  // Inicializar datos originales
  ventasPorCorteOriginal.value = JSON.parse(JSON.stringify(ventasPorCorte.value));
  
  // Cargar usuarios disponibles
  if (props?.users) {
    console.log(props?.users)
    usuariosDisponibles.value = props.users;
  }
})

// El filtro de sucursal ahora solo se aplica cuando el usuario hace clic en "Aplicar Filtro"
// watch(selectedSucursal, (newSucursalId) => {
//   if (newSucursalId) {
//     fetchFilteredData()
//   }
// })

const safeToFixed = (value) => {
  return parseFloat(value).toFixed(2)
}

const isEditing = ref(false)
const isCreating = ref(false)
const editedProducts = ref([])
const editedVentaId = ref(null)

// Formulario para crear/editar ventas
const ventaForm = ref({
  fechaHora: '',
  usuario_id: 0,
  metodo_pago: 'efectivo',
  factura: false,
  folio: '',
  productos: [],
  sucursal_id: '',
  fechaOriginalBackend: '' // Para almacenar la fecha original del backend
})

// Usuarios disponibles para seleccionar
const usuariosDisponibles = ref(props?.users || [])

const editVenta = (ventaId) => {
  const venta = ventas.value.find(v => v.id === ventaId);
  
  // Llenar el formulario con los datos de la venta
  // IMPORTANTE: Usar la fecha original de creación (venta.created_at) para mantener la integridad
  // Usar la función especializada para convertir la fecha del backend
  const fechaLocal = convertirFechaBackend(venta.created_at);
  
  // Debug: mostrar las fechas para verificar
  console.log('Fecha original del backend:', venta.created_at);
  console.log('Fecha convertida para datetime-local:', fechaLocal);
  
  // Debug: mostrar el valor del campo factura
  console.log('Valor factura del backend:', venta.factura, 'Tipo:', typeof venta.factura);
  
  // Normalizar el campo factura para asegurar que sea boolean
  const facturaNormalizada = venta.factura === true || venta.factura === 1 || venta.factura === '1';
  
  console.log('Factura normalizada:', facturaNormalizada);
  
  ventaForm.value = {
    fechaHora: fechaLocal, // Formato YYYY-MM-DDTHH:MM para datetime-local
    usuario_id: venta.usuario_id,
    metodo_pago: venta.metodo_pago,
    factura: facturaNormalizada,
    folio: venta.folio || '',
    productos: venta.detalles.map(detalle => ({
      producto_id: detalle.producto.id,
      cantidad: detalle.cantidad,
      precio_unitario: detalle.producto.precio,
      subtotal: detalle.cantidad * detalle.producto.precio
    })),
    sucursal_id: venta.sucursal_id,
    fechaOriginalBackend: venta.created_at // Almacenar la fecha original del backend
  };
  
  // Mostrar advertencia si la venta es de otra sucursal
  if (venta.sucursal_id !== props?.sucursal_id) {
    showToast("warning", `Editando venta de la sucursal ${venta.sucursal_id}. Puede cambiar la sucursal si es necesario.`);
    
    // Cargar usuarios de la sucursal de la venta para permitir la edición
    if (venta.sucursal_id) {
      loadUsuariosSucursal(venta.sucursal_id);
    }
  }
  
  editedVentaId.value = ventaId;
  isEditing.value = true;
  isCreating.value = false;
};

const crearNuevaVenta = () => {
  // Verificar que haya una sucursal seleccionada
  if (!props?.sucursal_id) {
    showToast("error", "Debe seleccionar una sucursal antes de crear una venta");
    return;
  }
  
  // Limpiar el formulario
  ventaForm.value = {
    fechaHora: new Date().toISOString().slice(0, 16),
    usuario_id: props?.usuario_id || '',
    metodo_pago: 'efectivo',
    factura: false,
    folio: '',
    productos: [],
    sucursal_id: props?.sucursal_id || '',
    fechaOriginalBackend: '' // Nueva venta no tiene fecha original
  };
  
  // Agregar un producto por defecto
  agregarProducto();
  
  isCreating.value = true;
  isEditing.value = false;
  editedVentaId.value = null;
};

const agregarProducto = () => {
  ventaForm.value.productos.push({
    producto_id: '',
    cantidad: 1,
    precio_unitario: 0,
    subtotal: 0
  });
};

const eliminarProducto = (index) => {
  ventaForm.value.productos.splice(index, 1);
  calcularTotal();
};

const actualizarPrecio = (index) => {
  const producto = ventaForm.value.productos[index];
  if (producto.producto_id) {
    const prodInventario = inventario.value.find(p => p.id == producto.producto_id);
    if (prodInventario) {
      producto.precio_unitario = prodInventario.precio;
      calcularSubtotal(index);
    }
  }
};

const calcularSubtotal = (index) => {
  const producto = ventaForm.value.productos[index];
  producto.subtotal = producto.cantidad * producto.precio_unitario;
  calcularTotal();
};

const calcularTotal = () => {
  console.log('Calculando total de la venta');
};

// Computed para el total de la venta
const totalVenta = computed(() => {
  return ventaForm.value.productos.reduce((total, producto) => {
    return total + (producto.subtotal || 0);
  }, 0);
});

const saveEditedVenta = async () => {
  try {
    // Validaciones básicas
    if (!ventaForm.value.sucursal_id) {
      showToast("error", "Debe seleccionar una sucursal");
      return;
    }
    
    if (!ventaForm.value.usuario_id) {
      showToast("error", "Debe seleccionar un usuario");
      return;
    }
    
    if (ventaForm.value.productos.length === 0) {
      showToast("error", "Debe agregar al menos un producto");
      return;
    }
    
    if (ventaForm.value.productos.some(p => !p.producto_id || !p.cantidad || p.cantidad <= 0)) {
      showToast("error", "Todos los productos deben tener cantidad válida");
      return;
    }
    
    
    // Validar que la fecha sea válida
    if (!ventaForm.value.fechaHora) {
      showToast("error", "Debe seleccionar una fecha y hora válida");
      return;
    }
    
    const ventaData = {
      fecha_hora: ventaForm.value.fechaHora,
      usuario_id: ventaForm.value.usuario_id,
      metodo_pago: ventaForm.value.metodo_pago,
      factura: ventaForm.value.factura,
      folio: ventaForm.value.folio,
      productos: ventaForm.value.productos,
      sucursal_id: ventaForm.value.sucursal_id
    };
    
    // Debug: mostrar la fecha que se está enviando
    console.log('Fecha original de la venta:', ventaForm.value.fechaHora);
    console.log('Fecha formateada para envío:', ventaData.fecha_hora);
    
    let response;
    if (isCreating.value) {
      // Crear nueva venta
      response = await axios.post('/ventas/crear', ventaData);
    } else {
      // Editar venta existente
      response = await axios.put(`/ventas/${editedVentaId.value}`, ventaData);
    }
    
    if (response.status === 200) {
      showToast("success", isCreating.value ? "Venta creada correctamente" : "Venta actualizada correctamente");
      
      cancelEdit();
      fetchFilteredData();
    } else {
      showToast("error", response.data.error || "Error al procesar la venta");
    }
  } catch (error) {
    console.error('Error al guardar venta:', error);
    showToast("error", error.response?.data?.error || "Error al procesar la venta");
  }
};

const handleNewCorte = async () => {
  savingNewCorte.value = true;
  try {
    const response =  await axios.post('/corte-caja/crear-corte', {
      dinero_inicio: 0
    });
    if (response.status === 200) {
      showToast("success", "Nuevo corte creado correctamente");
      initialCash.value = 0;
      finalCash.value = 0;
      window.location.reload();
    } else {
      showToast("error", response.data.error || "Error al crear nuevo corte");
    }
  } catch (error) {
    showToast("error", error.response || "Error al crear nuevo corte");
  } finally {
    savingNewCorte.value = false;
  }
}

const loadUsuariosSucursal = async (sucursalId) => {
  try {
    const response = await axios.get(`/api/usuarios-sucursal/${sucursalId}`);
    if (response.status === 200) {
      // Combinar usuarios de la sucursal específica con los usuarios disponibles
      const usuariosSucursal = response.data.usuarios || [];
      
      // Crear un Set para evitar duplicados
      const usuariosUnicos = new Set([...usuariosDisponibles.value.map(u => u.id), ...usuariosSucursal.map(u => u.id)]);
      
      // Combinar todos los usuarios únicos
      const todosUsuarios = [...usuariosDisponibles.value, ...usuariosSucursal];
      usuariosDisponibles.value = todosUsuarios.filter((usuario, index, self) => 
        index === self.findIndex(u => u.id === usuario.id)
      );
    }
  } catch (error) {
    console.error('Error al cargar usuarios de la sucursal:', error);
  }
};

const cancelEdit = () => {
  isEditing.value = false
  isCreating.value = false
  editedVentaId.value = null
  
  // Limpiar el formulario
  ventaForm.value = {
    fechaHora: '',
    usuario_id: '',
    metodo_pago: 'efectivo',
    factura: false,
    folio: '',
    productos: [],
    sucursal_id: props?.sucursal_id || '',
    fechaOriginalBackend: ''
  }
}

const deleteVenta = async (ventaId) => {
  try {
    const response = await axios.post('/ventas/eliminar', { venta_id: ventaId });
    if (response.status === 200) {
      showToast("success", response.data.message);
      if (response.data.renumeracion) {
        showToast("info", response.data.renumeracion);
      }
      fetchFilteredData(); // Recargar los datos para reflejar los cambios
    } else {
      showToast("error", response.data.error || "Error al eliminar la venta");
    }
  } catch (error) {
    console.error('Error al eliminar venta:', error);
    if (error.response && error.response.data) {
      showToast("error", error.response.data.error || "Error al eliminar la venta");
    } else {
      showToast("error", "Error al eliminar la venta");
    }
  }
}

const restaurarVenta = async (ventaId) => {
  try {
    const response = await axios.post('/ventas/restaurar', { venta_id: ventaId });
    if (response.status === 200) {
      showToast("success", response.data.message);
      fetchFilteredData(); // Recargar los datos para reflejar los cambios
    } else {
      showToast("error", response.data.error || "Error al restaurar la venta");
    }
  } catch (error) {
    console.error('Error al restaurar venta:', error);
    if (error.response && error.response.data) {
      showToast("error", error.response.data.error || "Error al restaurar la venta");
    } else {
      showToast("error", "Error al restaurar la venta");
    }
  }
}

const renumerarVentas = async () => {
  try {
    const response = await axios.post('/ventas/renumerar', { 
      sucursal_id: props?.sucursal_id,
      fecha: selectedDate.value 
    });
    if (response.status === 200) {
      showToast("success", response.data.message);
      //showToast("info", `Se renumeraron ${response.data.ventas_renumeradas} ventas`);
      fetchFilteredData(); // Recargar los datos para reflejar los cambios
    } else {
      showToast("error", response.data.error || "Error al renumerar las ventas");
    }
  } catch (error) {
    console.error('Error al renumerar ventas:', error);
    if (error.response && error.response.data) {
      showToast("error", error.response.data.error || "Error al renumerar las ventas");
    } else {
      showToast("error", "Error al renumerar las ventas");
    }
  }
}

const renumerarVentasManual = async () => {
  try {
    showToast("info", "🔄 Iniciando renumeración manual...");
    
    const response = await axios.post('/ventas/renumerar', { 
      sucursal_id: props?.sucursal_id || 1,
      fecha: new Date().toISOString().split('T')[0] // Fecha actual
    });
    
    if (response.status === 200) {
      showToast("success", response.data.message);
      
      // Mostrar detalles de la renumeración si están disponibles
      if (response.data.detalle_renumeracion) {
        const detalles = response.data.detalle_renumeracion;
        let mensaje = `Se renumeraron ${response.data.ventas_renumeradas} ventas:\n`;
        
        detalles.forEach(detalle => {
          mensaje += `• Venta ${detalle.id}: ${detalle.idVentaDia_anterior} → ${detalle.idVentaDia_nuevo} (${detalle.created_at})\n`;
        });
        
        //showToast("info", mensaje);
      }
      
      // Recargar los datos para reflejar los cambios
      fetchFilteredData();
    } else {
      showToast("error", response.data.error || "Error al renumerar ventas");
    }
  } catch (error) {
    console.error('Error al renumerar ventas manualmente:', error);
    if (error.response && error.response.data) {
      showToast("error", error.response.data.error || "Error al renumerar ventas");
    } else {
      showToast("error", "Error al renumerar ventas");
    }
  }
}

const updateVisibleVentas = ( id, visible ) => {
  // Actualizar el estado local inmediatamente para feedback visual
  const venta = ventas.value.find(v => v.id === id);
  if (venta) {
    venta.visible = visible;
  }
  
  // Hacer petición al backend para actualizar la base de datos
  axios.post('/ventas/actualizar-visible', {
    id: id,
    visible: visible,
  }).then(response => {
    if (response.status === 200) {
      showToast("success", response.data.message);
      fetchFilteredData();
    } else {
      showToast("error", response.data.error || "Error al actualizar las ventas");
    }
  }).catch(error => {
    // Revertir el cambio si hay error
    if (venta) {
      venta.visible = !visible;
    }
    showToast("error", "Error al actualizar la visibilidad");
    fetchFilteredData();
  });
}

const isRefoleandoVentasNormales = ref(false);

const refolearVentasNormales = async () => {

  //mandar sucursal y fecha
  try {
    isRefoleandoVentasNormales.value = true;
    const response = await axios.post('/ventas/renumerar-normales', { 
      sucursal_id: selectedSucursal.value,
      fecha: selectedDate.value
    });
    if (response.status === 200) {
      showToast("success", response.data.message);
      fetchFilteredData();
    } else {
      showToast("error", response.data.error || "Error al refolear ventas normales");
    }
  } catch (error) {
    console.error('Error al refolear ventas normales:', error);
    showToast("error", error.response?.data?.error || "Error al refolear ventas normales");
  } finally {
    isRefoleandoVentasNormales.value = false;
  }
}

</script>

<style>
.print {
    display: block !important;
}

.selected-corte {
  border: 2px solid #3b82f6; /* Resaltar con un borde azul */
}

@media print {
    .no-print {
        display: none !important;
    }

    .print {
        display: block !important;
    }

    body {
        overflow: scroll !important;
    }

    ::-webkit-scrollbar {
        display: none;
    }
    

    astro-dev-toolbar {
        display: none !important;
    }

    article {
        break-inside: avoid;
    }

    @page {
        size: A4 portrait;
        margin: 1cm;
        margin-top: 0.5cm;
    }


    .tabla {
        width: 100%;
        table-layout: fixed;
        font-size: 10px;
    }

    .tabla th, .tabla td {
        word-wrap: break-word;
        padding: 2px 4px;
        font-size: 10px;
    }

    /* Hacer la letra más compacta */
    body {
        font-size: 11px;
        line-height: 1.2;
        color: black !important;
    }

    text{
      color: black !important;
    }

    span{
      color: black !important;
    }

    h1 {
        font-size: 18px !important;
        margin-bottom: 10px !important;
    }

    h2 {
        font-size: 14px !important;
        margin-bottom: 8px !important;
    }

    h3 {
        font-size: 12px !important;
        margin-bottom: 6px !important;
    }

    /* Compactar espaciado en tablas */
    .bg-gray-100 {
        padding: 8px !important;
        margin-bottom: 8px !important;
    }

    .mb-8 {
        margin-bottom: 12px !important;
    }

    .mb-6 {
        margin-bottom: 10px !important;
    }

    .mb-4 {
        margin-bottom: 6px !important;
    }

    /* Compactar grid de información */
    .grid {
        gap: 8px !important;
    }

    /* Ajustar altura de contenedores */
    .h-screen {
        height: auto !important;
    }
}

@media print {
  .text-red-700 {
    color: #6b7280 !important; /* Cambiar a gris al imprimir */
  }
  .ventasR{
    height: fit-content !important;
    overflow: auto !important;
  }
}

</style>
