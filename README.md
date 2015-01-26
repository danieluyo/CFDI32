# CFDI32

CFDI32 es un paquete creado para poder generar xml preparados para poder generar facturas CFDI v. 3.2

## Uso basico

``` php
	
	<?php
	use Andradedev\Cfdi32\CFDI32;

	$cfdi32 = new CFDI32();

	$cfdi32->create($gen, $emi, $rec, $conc, $imp, $tipo, $comp = null);

	// $gen = datos generales
	// $emi = datos del emisor
	// $rec = datos del cliente
	// $conc = datos de los conceptos
	// $imp = datos de los impuestos
	// $tipo = tipo comprobante factura = 'F' o nomina = ’N'
	// $comp = datos de los complementos por ejemplo las percepciones de la nomina

```

Todos estos datos van en formato array a 
excepción del tipo que es string

``` php
	
	//ejemplo 
	$gen = [
			"serie"             => F,
			"folio"             => 1,
			"fecha"             => '2014-01-16T12:12:12',
			"formaDePago"       => "PAGO EN UNA SOLA EXHIBICION",
			"noCertificado"     => '12313241321243',
			"subTotal"          => 1100.00,
			"descuento"         => 0.00,
			"total"             => 1160.00,
			"tipoDeComprobante" => '',
			"metodoDePago"      => 'No identificado',
			"LugarExpedicion"   => "Merida, yucatan"
       ];

```

### Obtener xml

``` php

	$cfdi->getXML(); devuelve un string que contiene el xml

```
### guardar xml en archivo

``` php

	$cfdi32->save(name = null); Te guarda el xml por default 'cfdi/xml/' 

```

#### Actualizando readme