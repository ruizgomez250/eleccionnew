@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Registrar Orden de Compra</h1>
@stop
{{-- e
pc --}}

@section('content') 
    <div class="row">
        <div class="col-12">
            <div class="card" style="background-color:#D3D4D5;">
                <div class="card-body">
                    <form id="miFormulario" action="{{ route('compras.store') }}" method="post" autocomplete="off">
                        @csrf
                        @method('POST')
                        {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}
                        <div class="row">
                            <div class="row col-10">

                                <x-adminlte-input type="number" id="numero_orden" name="numero_orden"
                                    placeholder="Nº de ORDEN" label="ORDEN Nº" fgroup-class="col-md-2" required />
                                {{-- Fecha de Emisión de la Factura. --}}
                                <x-adminlte-input-date label="FECHA ORDEN" name="fecha" id="fecha" type="date"
                                    value="{{ now()->format('Y-m-d') }}" fgroup-class="col-md-2" required />


                                {{-- Nombre o Razón Social del Emisor (nombre de la empresa o persona que emite la factura). --}}

                                {{-- Número de Factura. --}}
                                <x-adminlte-input-date  name="plazo_entrega" id="plazo_entrega"
                                    type="hidden" value="{{ now()->format('Y-m-d') }}"  required />
                                <x-adminlte-input-date label="FECHA OFERTA" name="fecha_oferta" id="fecha_oferta"
                                    type="date" value="{{ now()->format('Y-m-d') }}" fgroup-class="col-md-2" required />

                            </div>
                            <div class="row col-10">
                                {{-- <x-adminlte-input label="PROVEEDOR" name="proveedor" id="proveedor" type="text"
                                    value="" fgroup-class="autocomplete-proveedor col-md-2" required /> --}}
                                    
                                {{-- Nombre o Razón Social del Emisor (nombre de la empresa o persona que emite la factura). --}}
                                <x-adminlte-input type="number" id="cod_proveedor" name="cod_proveedor"
                                    onchange="cambiarCod()" placeholder="Codigo" label="COD. PROV." fgroup-class="col-md-2"
                                    required />
                                <x-adminlte-select2 name="id_proveedor" id="id_proveedor" label="PROVEEDOR"
                                    data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-4"
                                    onchange="actualizarNumeroDocumento()">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-red">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                    </x-slot>
                                    @foreach ($proveedor as $item)
                                        <option value={{ $item->id }} data-ruc="{{ $item->ruc }}">
                                            {{ $item->razonsocial }}</option>
                                    @endforeach
                                </x-adminlte-select2>
                                {{-- Número de Identificación Tributaria del Emisor (RUC). --}}
                                <x-adminlte-input type="text" id="numero_documento" name="numero_documento"
                                    placeholder="RUC" label="RUC" readonly fgroup-class="col-md-2" />
                                    

                            </div>
                            <div class="row col-10">
                                <x-adminlte-input type="number" id="cod_solicitante" name="cod_solicitante"
                                    onchange="cambiarCodSolic()" placeholder="Codigo" label="COD. SOLIC." fgroup-class="col-md-2"
                                    required />
                                <x-adminlte-select2 name="id_secciones" id="id_secciones" label="SECCION"
                                    data-placeholder="Seleccionar una seccion..." fgroup-class="col-md-4">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-primary">
                                            <i class="fas fa-building"></i>
                                        </div>
                                    </x-slot>
                                    @foreach ($seccion as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->codigo . ' ' . $item->descripcion }}</option>
                                    @endforeach
                                </x-adminlte-select2>
                            </div>
                            <div class="row col-10">
                                <x-adminlte-input type="number" id="numero_factura" name="numero_factura"
                                    placeholder="Nº de FACTURA" label="Factura Nº" fgroup-class="col-md-2" required />
                                <x-adminlte-input-date label="FECHA FACTURA" name="fecha_factura" id="fecha_factura"
                                    type="date" value="{{ now()->format('Y-m-d') }}" fgroup-class="col-md-2" required />
                                <x-adminlte-input-date label="ENTIDAD" name="entidad" id="entidad" type="text"
                                    value="HONORABLE CAMARA DE DIPUTADOS" fgroup-class="col-md-6" required />
                                    <x-adminlte-input type="number" id="numero_remision" name="numero_remision"
                                    placeholder="Nº de Remision" label="REMISION Nº" fgroup-class="col-md-2" required />
                                <x-adminlte-input type="hidden" id="numero_recepcion" name="numero_recepcion"
                                    placeholder="Nº de Recepcion"  value="1" required />
                                <x-adminlte-input type="hidden" id="numero_nota_pedido_interno" value="1"
                                    name="numero_nota_pedido_interno" placeholder="Nº de Nota"  required />
                                <x-adminlte-input type="hidden" id="numero_solicitud" name="numero_solicitud"
                                    placeholder="Nº de solicitud"  value="1"
                                    required />
                            </div>
                           


                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check">
                                                        <input type="radio" class="form-check-input" id="inlineRadio1"
                                                            name="tipocompra" value="0">Exentas
                                                    </div>
                                                    <div class="form-check ml-2">
                                                        <input type="radio" class="form-check-input" id="inlineRadio2"
                                                            name="tipocompra" value="1">5%
                                                    </div>
                                                    <div class="form-check ml-2">
                                                        <input type="radio" class="form-check-input" id="inlineRadio3"
                                                            name="tipocompra" value="2" checked>10%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>




                        <div id="items">
                            <div class="item" style="background-color: yellow;">
                                <div class="row ml-2">
                                    <label for="" class="col-1">ITEM</label>
                                    <label for="" class="col-1">UNDM</label>
                                    <label for="" class="col-1">CÓDIGO</label>
                                    <label for="" class="col-1">CANTIDAD</label>
                                    <label for="" class="col-3">DESCRIPCION</label>
                                    <label for="" class="col-1">PRECIO UNITARIO</label>
                                    <label for="" class="col-1">EXENTAS</label>
                                    <label for="" class="col-1">5%</label>
                                    <label for="" class="col-1">10%</label>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <!-- Agrega este elemento para mostrar la suma total -->
                            <div>Suma Total: <span id="total-sum">0</span></div>
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger mx-1" style="float: right;"
                                    href="{{ route('compras.index') }}">Cancelar</a>
                                <x-adminlte-button class="btn-group" style="float: right;" type="submit" label="Grabar"
                                    theme="primary" icon="fas fa-fw fa-lg fa-save" />
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script src="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.js') }}"></script>
    <script>
        $inicio=1;
        $(document).ready(function() {
            // Enfocar el campo de entrada de fecha al cargar la página
            $('#numero_orden').focus();
        });
        $('input[name="numero_orden"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="fecha"]').focus();
            }
        });
        $('input[name="fecha"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="fecha_oferta"]').focus();
            }
        });
        
        $('input[name="fecha_oferta"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Abre el select2 y enfoca en él
                //$('#id_proveedor').select2('open');
                $('input[name="cod_proveedor"]').focus();
            }
        });
        $('input[name="cod_proveedor"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Abre el select2 y enfoca en él
                //$('#id_proveedor').select2('open');
                $('input[name="cod_solicitante"]').focus();
            }
        });
        $('input[name="cod_solicitante"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Abre el select2 y enfoca en él
                //$('#id_proveedor').select2('open');
                $('input[name="numero_factura"]').focus();
            }
        });
        
        $('input[name="numero_factura"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="fecha_factura"]').focus();
            }
        });
        $('input[name="fecha_factura"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="entidad"]').focus();
            }
        });
        $('input[name="entidad"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="numero_remision"]').focus();
            }
        });
        $('input[name="numero_remision"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="codigo1[]"]').focus();
            }
        });
        
        
        
        $(document).on("focus", ".autocomplete-proveedor", function() {
            var productos = @json($proveedor);

            $(this).autocomplete({
                source: productos.map(function(producto) {
                    return {
                        label: producto.razonsocial,
                        value: producto.razonsocial,
                        codigo: producto.id,
                        id: producto.id,
                    };
                }),
                change: function(event, ui) {
                    if (!ui.item) {
                        // Si no se selecciona ninguna opción, borrar el campo
                        $(this).val('');
                    }
                }
            });
        });
        actualizarNumeroDocumento();
        function cambiarCod() {
            // Obtener el valor del campo "cod_proveedor"
            var nuevoCod = $('input[name="cod_proveedor"]').val();

            // Buscar la opción en el select con id "id_proveedor" que tenga el nuevo código
            var select2 = document.getElementById("id_proveedor");
            var options = select2.options;

            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                var dataCod = option.value; // Suponiendo que el valor de la opción es el código a buscar

                // Verificar si encontramos una coincidencia
                if (dataCod === nuevoCod) {
                    // Cambiar el valor seleccionado en el select
                    select2.value = option.value;
                    $('#id_proveedor').val(dataCod).trigger('change.select2');
                    // Llamar a la función actualizarNumeroDocumento
                    actualizarNumeroDocumento();
                    return; // Salir de la función después de encontrar una coincidencia
                }
            }

            // Si no se encontró una coincidencia, llamar a la función actualizarNumeroDocumento
            actualizarNumeroDocumento();
        }
        function cambiarCodSolic() {
            // Obtener el valor del campo "cod_proveedor"
            var nuevoCod = $('input[name="cod_solicitante"]').val();

            // Buscar la opción en el select con id "id_proveedor" que tenga el nuevo código
            var select2 = document.getElementById("id_secciones");
            var options = select2.options;

            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                var dataCod = option.value; // Suponiendo que el valor de la opción es el código a buscar

                // Verificar si encontramos una coincidencia
                if (dataCod === nuevoCod) {
                    // Cambiar el valor seleccionado en el select
                    select2.value = option.value;
                    $('#id_secciones').val(dataCod).trigger('change.select2');
                    // Llamar a la función actualizarNumeroDocumento
                    actualizarDependencia();
                    return; // Salir de la función después de encontrar una coincidencia
                }
            }

            // Si no se encontró una coincidencia, llamar a la función actualizarNumeroDocumento
            actualizarDependencia();
        }
        actualizarDependencia();
        function actualizarDependencia() {
            var select2 = document.getElementById("id_secciones");
            var numeroDocumentoInput = document.getElementById("dependencia_solicitante");
            var selectedOption = select2.options[select2.selectedIndex];
            var numeroDocumento = selectedOption.getAttribute("data-desc");
            var cod_proveedor = selectedOption.value;
            document.getElementById("cod_solicitante").value = cod_proveedor;

        }

        $(document).ready(function() {
            $('#miFormulario').on('keydown', 'input', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Evita que el formulario se envíe
                }
            });
        });

        function sanitizeInput(input) {
            // Obtén el valor actual del campo de entrada
            let value = input.value;

            // Elimina cualquier carácter que no sea un número o un punto decimal
            value = value.replace(/[^0-9.]/g, '');

            // Reemplaza comas por puntos para números decimales
            value = value.replace(/,/g, '.');

            // Actualiza el valor del campo de entrada
            input.value = value;
        }



        function cambiarDescripcion(inputCodigo) {
            var codigoValue = inputCodigo.value;
            $.ajax({
                url: '{{ route('obtenercodobjeto') }}',
                method: 'POST',
                data: {
                    codigo: codigoValue,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.objeto) {
                        var descripcion = response.objeto;
                        var id = response.id;
                        $(inputCodigo).closest('.row').find('input[name="descripcion[]"]').val(
                            descripcion);
                        $(inputCodigo).closest('.row').find('input[name="codigo[]"]').val(
                            id);
                    } else {
                        inputCodigo.value = '';
                    }
                },
                error: function(error) {
                    console.error('Error en la petición AJAX:', error);
                }
            });
        }

        function cambiarCodigo(inputProducto) {

            // Obtén el valor del producto desde el elemento actual
            var productoId = '';
            if ($(inputProducto).data('ui-autocomplete').selectedItem) {
                productoId = $(inputProducto).data('ui-autocomplete').selectedItem.id;
            }


            // Encuentra el input de código dentro del contenedor actual y establece el valor
            
            $(inputProducto).closest('.row').find('input[name="codigo1[]"]').val(productoId);
            //$(inputProducto).closest('.row').find('input[name="codigo[]"]');
            $.ajax({
                url: '{{ route('obtenercodobjeto') }}',
                method: 'POST',
                data: {
                    codigo: productoId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.objeto) {
                        var id = response.id;
                        $(inputProducto).closest('.row').find('input[name="codigo[]"]').val(
                            id);
                    }
                },
                error: function(error) {
                    console.error('Error en la petición AJAX:', error);
                }
            });
        }
        $(document).on("focus", ".autocomplete-producto", function() {
            var productos = @json($objeto);

            $(this).autocomplete({
                source: productos.map(function(producto) {
                    return {
                        label: producto.objeto,
                        value: producto.objeto,
                        codigo: producto.codigo,
                        id: producto.codigo,
                    };
                }),
                change: function(event, ui) {
                    if (!ui.item) {
                        // Si no se selecciona ninguna opción, borrar el campo
                        $(this).val('');
                    }
                }
            });
        });


        // Script para seleccionar RUC


        function actualizarNumeroDocumento() {
            var select2 = document.getElementById("id_proveedor");
            var numeroDocumentoInput = document.getElementById("numero_documento");
            var selectedOption = select2.options[select2.selectedIndex];
            var numeroDocumento = selectedOption.getAttribute("data-ruc");
            numeroDocumentoInput.value = numeroDocumento;
            var cod_proveedor = selectedOption.value;
            document.getElementById("cod_proveedor").value = cod_proveedor;
        }
        // Script para agregar y eliminar dinámicamente ítems de compra
        document.addEventListener("DOMContentLoaded", function() {
            const btnAddItem = document.getElementById("btn-add-item");
            const itemsContainer = document.getElementById("items");
            const totalSumElement = document.getElementById("total-sum");
            let totalSum = 0;
            addNewItem();
            // Función para agregar un nuevo ítem de compra
            function addNewItem() {
                var radioSeleccionado = document.querySelector('input[name="tipocompra"]:checked');
                var tipocompra = radioSeleccionado.value;


                const newItem = document.createElement("div");
                newItem.classList.add("item");
                borrar = ($inicio ==0) ? '<button class="btn-remove btn btn-outline-danger ml-2" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>' : '';
                exent =
                    '<input type="number" name="exenta[]" value="0" class="form-control col-1" placeholder="Exenta" required readonly>';
                cinc =
                    '<input type="number" name="cinco[]" value="0" class="form-control col-1" placeholder="iva 5%" required readonly>';
                die =
                    '<input type="number" name="diez[]" value="0" class="form-control col-1" placeholder="iva 10%" required readonly>';

                switch (tipocompra) {
                    case '0':
                        exent =
                            '<input type="text" name="exenta[]" value="0" class="form-control col-1" placeholder="Exenta" required oninput="sanitizeInput(this)">';
                        break;
                    case '1':
                        cinc =
                            '<input type="text" name="cinco[]" value="0" class="form-control col-1" placeholder="iva 5%" required oninput="sanitizeInput(this)">';
                        break;
                    case '2':
                        die =
                            '<input type="text" name="diez[]" value="0" class="form-control col-1" placeholder="iva 10%" required oninput="sanitizeInput(this)">';
                        break;
                }
                newItem.innerHTML = `
                <div class="row ml-1">
                                    <input type="number" name="item[]" class="codigo_id form-control col-1"
                                    placeholder="Código" value="1" required readonly>
                                    <input type="text" name="unidad[]" value="UNIDAD" class="codigo_id form-control col-1"
                                    placeholder="U. medida" value="" required >
                                    <input type="hidden" name="iva[]" class="codigo_id form-control col-2"
                                    placeholder="Código" value="`+tipocompra+`" required readonly>
                                    <input type="hidden" name="codigo[]" class="codigo_id form-control col-1" required>
                                    <input type="text" name="codigo1[]" class="codigo_id form-control col-1"
                                    placeholder="Código" onchange="cambiarDescripcion(this)" value="" required>
                                    <input type="text" name="cantidad[]" step="any" class="form-control col-1"
                                    placeholder="Cantidad"  value =""  required required required oninput="sanitizeInput(this)">
                                    <input type="text" name="descripcion[]" class="autocomplete-producto form-control col-3 "
                                        placeholder="Descripcion" onchange="cambiarCodigo(this)" value="" required style="font-size: 12px;>
                                    <input type="hidden" name="productoid[]" class="producto_id form-control col-2"
                                        required>
                                    <input type="text" name="precio[]" value="" class="form-control col-1" placeholder="Precio "
                                        required oninput="sanitizeInput(this)">
                                        ` + exent + cinc + die + `
                                        
                                </div>
            `;
            $inicio=0;

                const codigoInput = newItem.querySelector('input[name="codigo1[]"]');
                codigoInput.addEventListener('keydown', function(e) {
                    // Verifica si la tecla presionada es "Enter"
                    if (e.key === 'Enter') {
                        // Evita el envío del formulario
                        e.preventDefault();
                        // Enfoca en el campo de producto
                        const cantidadInput = newItem.querySelector('input[name="cantidad[]"]');
                        cantidadInput.focus();
                    }
                });
                const cantidadInput = newItem.querySelector('input[name="cantidad[]"]');
                cantidadInput.addEventListener('keydown', function(e) {
                    // Verifica si la tecla presionada es "Enter"
                    if (e.key === 'Enter') {
                        // Evita el envío del formulario
                        e.preventDefault();
                        // Enfoca en el campo de producto
                        const descripcionInput = newItem.querySelector('input[name="descripcion[]"]');
                        descripcionInput.focus();
                    }
                });
                const descripcionInput = newItem.querySelector('input[name="descripcion[]"]');
                descripcionInput.addEventListener('keydown', function(e) {
                    // Verifica si la tecla presionada es "Enter"
                    if (e.key === 'Enter') {
                        // Evita el envío del formulario
                        e.preventDefault();
                        // Enfoca en el campo de producto
                        const precioInput = newItem.querySelector('input[name="precio[]"]');
                        precioInput.focus();
                    }
                });
                const precioInput = newItem.querySelector('input[name="precio[]"]');
                precioInput.addEventListener('keydown', function(e) {
                    // Verifica si la tecla presionada es "Enter"
                    if (e.key === 'Enter') {
                        // Evita el envío del formulario
                        e.preventDefault();
                        // Enfoca en el campo de producto
                        const exentaInput = newItem.querySelector('input[name="exenta[]"]');
                        exentaInput.focus();
                    }
                });
                const exentaInput = newItem.querySelector('input[name="exenta[]"]');
                exentaInput.addEventListener('keydown', function(e) {
                    // Verifica si la tecla presionada es "Enter"
                    if (e.key === 'Enter') {
                        // Evita el envío del formulario
                        e.preventDefault();
                        // Enfoca en el campo de producto
                        const cincoInput = newItem.querySelector('input[name="cinco[]"]');
                        cincoInput.focus();
                    }
                });
                const cincoInput = newItem.querySelector('input[name="cinco[]"]');
                cincoInput.addEventListener('keydown', function(e) {
                    // Verifica si la tecla presionada es "Enter"
                    if (e.key === 'Enter') {
                        // Evita el envío del formulario
                        e.preventDefault();
                        // Enfoca en el campo de producto
                        const diezInput = newItem.querySelector('input[name="diez[]"]');
                        diezInput.focus();
                    }
                });
                const diezInput = newItem.querySelector('input[name="diez[]"]');
                diezInput.addEventListener('keydown', function(e) {
                    // Verifica si la tecla presionada es "Enter"
                    if (e.key === 'Enter') {
                        addNewItem();
                        
                    }
                });
                itemsContainer.appendChild(newItem);
                
                // Agregar el evento click para eliminar el ítem
                const btnRemove = newItem.querySelector(".btn-remove");
                btnRemove.addEventListener("click", function() {
                    removeItem(newItem);
                });

                const priceInput = newItem.querySelector('input[name="precio[]"]');
                priceInput.addEventListener("input", actualizarSumaTotal);

                const cantiInput = newItem.querySelector('input[name="cantidad[]"]');
                cantiInput.addEventListener("input", actualizarSumaTotal);
                actualizarSumaTotal();
                $('input[name="codigo1[]"]').focus();


            }



            function actualizarSumaTotal() {
                totalSum = 0;
                const priceInputs = document.getElementsByName("precio[]"); //trae todos los precios para recorrer
                const cantidadInputs = document.getElementsByName(
                    'cantidad[]'); //trae todas las cantidades para recorrer
                const ivaInputs = document.getElementsByName(
                    "iva[]"); //trae todos los impuestos para ver si es exenta iva 5 o iva 10
                const exentaInputs = document.getElementsByName(
                    "exenta[]"); //trae todos los totales exentas
                const itemInputs = document.getElementsByName(
                    "item[]"); //trae todos los totales exentas
                const cincoInputs = document.getElementsByName(
                    "cinco[]"); //trae todos los totales cinco
                const diezInputs = document.getElementsByName(
                    "diez[]"); //trae todos los totales diez
                // Itera a través de los elementos utilizando un bucle for
                itemN = 0;
                for (let i = 0; i < priceInputs.length; i++) {
                    const input = priceInputs[i];
                    const price = parseFloat(input.value) || 0;

                    const cantidadV = cantidadInputs[i];
                    const cantidad = parseFloat(cantidadV.value) || 0;

                    const ivaV = ivaInputs[i];
                    const iva = parseFloat(ivaV.value) || 0;

                    itemN++;
                    const itemV = itemInputs[i];
                    itemV.value = itemN;

                    const exentaV = exentaInputs[i];

                    const cincoV = cincoInputs[i];

                    const diezV = diezInputs[i];

                    tot = cantidad * price;
                    switch (iva) {
                        case 0:
                            exentaV.value = tot;
                            cincoV.value = 0;
                            diezV.value = 0;
                            break;
                        case 1:
                            exentaV.value = 0;
                            cincoporc = 0;
                            tot = tot + cincoporc
                            cincoV.value = tot;
                            diezV.value = 0;
                            break;
                        case 2:
                            exentaV.value = 0;
                            cincoV.value = 0;
                            diezporc = 0;
                            tot = tot + diezporc;
                            diezV.value = tot;
                            break;
                        default:
                            // Hacer algo si iva no coincide con ningún caso
                    }


                    totalSum += tot;
                }

                totalSumElement.textContent = totalSum.toFixed(2); // Mostrar la suma con dos decimales
            }



            function removeItem(itemToRemove) {
                itemsContainer.removeChild(itemToRemove);
                actualizarSumaTotal();
            }

            // Agregar el evento click para agregar un nuevo ítem
            //btnAddItem.addEventListener("click", addNewItem);
        });
    </script>
@endpush
