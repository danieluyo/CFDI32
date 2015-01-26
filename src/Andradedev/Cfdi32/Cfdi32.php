<?php namespace Andradedev\Cfdi32;

use DOMdocument;

class Cfdi32{

	protected $xml;
	protected $root;

	public function __construct() {
		$this->xml = new DOMdocument("1.0","UTF-8");
	}

	public static function create($gen, $emi, $rec, $conc, $imp, $tipo, $comp = null) {
		$this->setGenerales($gen, $tipo);
		$this->setEmisor($emi);
		$this->setReceptor($rec);
		$this->setConceptos($conc);
		$this->setImpuestos($imp);
		if ($comp !== null) {
			$this->setComplementos($comp);
		}

		$this->setCadenaOriginal();
		return $this;
	}

	public function setGenerales($gen, $tipo) {
		$this->root = $this->xml->createElement("cfdi:Comprobante");
		$this->root = $this->xml->appendChild($this->root);

		$this->setComprobante($tipo);
		 
		$this->setAttr($this->root, [
			"version" 			=>"3.2",
			"serie"             =>$gen['serie'],
			"folio"             =>$gen['folio'],
			"fecha"             =>$gen['fecha'],
			"formaDePago"       =>$gen['formaDePago']?:"PAGO EN UNA SOLA EXHIBICION",
			"noCertificado"     =>$gen['noCertificado'],
			"subTotal"          =>$gen['subTotal'],
			"descuento"         =>$gen['descuento'],
			"total"             =>$gen['total'],
			"tipoDeComprobante" =>$gen['tipoDeComprobante'],
			"metodoDePago"      =>$gen['metodoDePago'],
			"LugarExpedicion"   =>$gen['LugarExpedicion']?:"Merida, yucatan"
       ]);
	}

	public function setComprobante($tipo)
	{
		foreach ($this->getComprobante() as $comprobante) {
			if ($comprobante == $tipo) {
				$this->setAttr($this->root, \Config::get("cfdi32::comprobante.{$tipo}"));
				break;
			}
		}

		throw new Exception("Comprobante invalido!", 1);
	}

	public function getComprobante()
	{
		return ["F", "N"];
	}

	public function setEmisor($emi) {
		$emisor = $this->xml->createElement("cfdi:Emisor");
		$emisor = $this->root->appendChild($emisor);

		$this->setAttr($emisor, [
			"rfc"    =>$emi['rfc'],
			"nombre" =>$emi['nombre']
       	]);

		$domfis = $this->xml->createElement("cfdi:DomicilioFiscal");
		$domfis = $emisor->appendChild($domfis);
		$this->setAttr($domfis, [
			"calle"        =>$emi['calle'],
			"noExterior"   =>$emi['noExterior'],
			"noInterior"   =>$emi['noInterior']?:"",
			"colonia"      =>$emi['colonia'],
			"municipio"    =>$emi['municipio'],
			"estado"       =>$emi['estado'],
			"pais"         =>$emi['pais'],
			"codigoPostal" =>$emi['codigoPostal']
       	]);
		$expedidoen = $this->xml->createElement("cfdi:ExpedidoEn");
		$expedidoen = $emisor->appendChild($expedidoen);

		$this->setAttr($expedidoen, [
			"calle"        =>$emi['calle'],
			"noExterior"   =>$emi['noExterior'],
			"noInterior"   =>$emi['noInterior']?:"",
			"colonia"      =>$emi['colonia'],
			"localidad"    =>$emi['localidad'],
			"municipio"    =>$emi['municipio'],
			"estado"       =>$emi['estado'],
			"pais"         =>$emi['pais'],
			"codigoPostal" =>$emi['codigoPostal']
       	]);

		$regimen = $this->xml->createElement("cfdi:RegimenFiscal");
		$expedido = $emisor->appendChild($regimen);
		$this->setAttr($regimen, ["Regimen"=>$emi["regimen"]]);
	}

	public function setReceptor($rec) {
		$receptor = $this->xml->createElement("cfdi:Receptor");
		$receptor = $this->root->appendChild($receptor);
		$this->setAttr($receptor, [
				"rfc"    => $rec["rfc"],
				"nombre" => $rec["nombre"]
          	]);

		$domicilio = $this->xml->createElement("cfdi:Domicilio");
		$domicilio = $receptor->appendChild($domicilio);
		$this->setAttr($domicilio, [
				"calle"        => $rec["calle"],
				"noExterior"   => $rec["noExterior"],
				"noInterior"   => $rec["noInterior"]?:"",
				"colonia"      => $rec["colonia"],
				"municipio"    => $rec["municipio"],
				"estado"       => $rec["estado"],
				"pais"         => $rec["pais"],
				"codigoPostal" => $rec["codigoPostal"]
			]);
	}

	public function setConceptos($conc) {
		$conceptos = $this->xml->createElement("cfdi:Conceptos");
		$conceptos = $this->root->appendChild($conceptos);
		for ($i =0; $i<=count($conc)-1; $i++) {
		    $concepto = $this->xml->createElement("cfdi:Concepto");
		    $concepto = $conceptos->appendChild($concepto);
		    $this->setAttr($concepto, [
				"cantidad"      => $conc[$i]["cantidad"],
				"unidad"        => $conc[$i]["unidad"],
				"descripcion"   => $conc[$i]["descripcion"],
				"valorUnitario" => $conc[$i]["valor"],
				"importe"       => $conc[$i]["importe"]
			]);
		}
	}

	public function setImpuestos($imp) {
		$impuestos = $this->xml->createElement("cfdi:Impuestos");
		$impuestos = $this->root->appendChild($impuestos);
		if (array_key_exists("Retenciones", $imp)) {
		    $Retenciones = $this->xml->createElement("cfdi:Retenciones");
		    $Retenciones = $impuestos->appendChild($Retenciones);

		    foreach($imp['Retenciones'] as $ret) { 
			    $Retencion = $this->xml->createElement("cfdi:Retencion");
			    $Retencion = $Retenciones->appendChild($Retencion);

			    $this->setAttr($Retencion, [
					"importe"  =>$ret["importe"],
					"impuesto" =>$ret["impuesto"]
                ]);
		    }
		$impuestos->SetAttribute("totalImpuestosRetenidos",$imp['totalret']);
		}

		if (array_key_exists('Traslados', $imp)) {
		    $traslados = $this->xml->createElement("cfdi:Traslados");
		    $traslados = $impuestos->appendChild($traslados);
		    foreach($imp['Traslados'] as $tra) { 
			    $traslado = $this->xml->createElement("cfdi:Traslado");
			    $traslado = $traslados->appendChild($traslado);
			    $this->setAttr($traslado, [
					"impuesto" =>$tra["impuesto"],
					"tasa"     =>$tra["tasa"],
					"importe"  =>$tra["importe"]
         		]);
		    }
			$impuestos->SetAttribute("totalImpuestosTrasladados",$imp['totaltra']);
		}
	}

	public function setComplementos($comp) {
		$complementos = $this->xml->createElement("cfdi:Complemento");
		$complementos = $this->root->appendChild($complementos);

		$nomina = $this->xml->createElement("nomina:Nomina");
	    $nomina = $complementos->appendChild($nomina);

	    $comp['percepcion'][0] = number_format($comp['percepcion'][0], 6, ".", "");
	    $comp['percepcion'][1] = number_format($comp['percepcion'][1], 6, ".", "");

	    $Percepciones = $this->xml->createElement("nomina:Percepciones");
	    $Percepciones = $nomina->appendChild($Percepciones);
	    $this->setAttr($Percepciones, ["TotalGravado" => $comp['percepcion'][0], "TotalExento" => $comp['percepcion'][1]]);

	    for ($i=0; $i <= count($comp['percepciones'])-1 ; $i++) { 
		    $Percepcion = $this->xml->createElement("nomina:Percepcion");
		    $Percepcion = $Percepciones->appendChild($Percepcion);

		    $comp['percepciones'][$i]["impe"] = number_format($comp['percepciones'][$i]["impe"], 6, ".", "");
	    	$comp['percepciones'][$i]["impg"] = number_format($comp['percepciones'][$i]["impg"], 6, ".", "");

		    $this->setAttr($Percepcion, [
				"TipoPercepcion" =>$comp['percepciones'][$i]["tipo"],
				"Clave"          =>$comp['percepciones'][$i]["clave"],
				"Concepto"       =>$comp['percepciones'][$i]["concepto"],
				"ImporteExento"  =>$comp['percepciones'][$i]["impe"],
				"ImporteGravado" =>$comp['percepciones'][$i]["impg"]
            ]);
	    }

	    $Deducciones = $this->xml->createElement("nomina:Deducciones");
	    $Deducciones = $nomina->appendChild($Deducciones);

	    $comp['deduccion'][0] = number_format($comp['deduccion'][0], 6, ".", "");
	    $comp['deduccion'][1] = number_format($comp['deduccion'][1], 6, ".", "");

	    $this->setAttr($Deducciones, ["TotalGravado" => $comp['deduccion'][0], "TotalExento" => $comp['deduccion'][1]]);

	    for ($i=0; $i <= count($comp['deducciones'])-1 ; $i++) { 
		    $Deduccion = $this->xml->createElement("nomina:Deduccion");
		    $Deduccion = $Deducciones->appendChild($Deduccion);

		    $comp['deducciones'][$i]["impe"] = number_format($comp['deducciones'][$i]["impe"], 6, ".", "");
	    	$comp['deducciones'][$i]["impg"] = number_format($comp['deducciones'][$i]["impg"], 6, ".", "");

		    $this->setAttr($Deduccion, [
				"TipoDeduccion"  =>$comp['deducciones'][$i]["tipo"],
				"Clave"          =>$comp['deducciones'][$i]["clave"],
				"Concepto"       =>$comp['deducciones'][$i]["concepto"],
				"ImporteExento"  =>$comp['deducciones'][$i]["impe"],
				"ImporteGravado" =>$comp['deducciones'][$i]["impg"]
            ]);
	    }
	    if (array_key_exists('incapacidades', $comp)) {
		    $Incapacidades = $this->xml->createElement("nomina:Incapacidades");
		    $Incapacidades = $nomina->appendChild($Incapacidades);
		    for ($i=0; $i <= count($comp['incapacidades'])-1 ; $i++) { 

		    	$comp['incapacidades'][$i]["descuento"] = number_format($comp['incapacidades'][$i]["descuento"], 6, ".", "");

			    $Incapacidad = $this->xml->createElement("nomina:Incapacidades");
			    $Incapacidad = $Incapacidades->appendChild($Incapacidad);
			    $this->setAttr($Incapacidades, [
					"DiasIncapacidad" =>$comp['incapacidades'][$i]["dias"],
					"TipoIncapacidad" =>$comp['incapacidades'][$i]["tipo"],
					"Descuento"       =>$comp['incapacidades'][$i]["descuento"]
                ]);
		    }
	    }

	    if (array_key_exists('horas', $comp)) {
		    $HorasExtras = $this->xml->createElement("nomina:HorasExtras");
		    $HorasExtras = $nomina->appendChild($HorasExtras);
		    for ($i=0; $i <= count($comp['horas'])-1 ; $i++) { 
		    	$comp['horas'][$i]["importe"] = number_format($comp['horas'][$i]["importe"], 6, ".", "");
			    $HorasExtra = $this->xml->createElement("nomina:HorasExtra");
			    $HorasExtra = $HorasExtras->appendChild($HorasExtra);
			    $this->setAttr($HorasExtra, [
					"Dias"          =>$comp['horas'][$i]["dias"],
					"TipoHoras"     =>$comp['horas'][$i]["tipo"],
					"HorasExtra"    =>$comp['horas'][$i]["horas"],
					"ImportePagado" =>$comp['horas'][$i]["importe"]
                ]);
		    }
	    }

		$this->setAttr($nomina, $comp["attr"]);
	}

	public function setCadenaOriginal() {
		$xsl = new DOMDocument;
		$xsl->load(\Config::get("cfdi32::config.xslt"));

		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);

		$this->cadena_original = $proc->transformToXML($this->getXML());

		return $this;
	}

	public function getCadenaOriginal()
	{
		return $this->cadena_original;
	}

	public function setSello() {
		$pkeyid = openssl_get_privatekey(file_get_contents(\Config::get("cfdi32::config.key")));

		openssl_sign($this->cadena_original, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
		
		openssl_free_key($pkeyid);
		 
		$this->sello = base64_encode($crypttext);      
		$this->root->setAttribute("sello",$this->sello);

		$datos = file(\Config::get("cfdi32::config.cer"));
		$certificado = ""; $carga=false;
		for ($i=0; $i<sizeof($datos); $i++) {
		    if (strstr($datos[$i],"END CERTIFICATE")) $carga=false;
		    if ($carga) $certificado .= trim($datos[$i]);
		    if (strstr($datos[$i],"BEGIN CERTIFICATE")) $carga=true;
		}

		$this->root->setAttribute("certificado",$certificado);

		return $this;
	}

	public function getSello() 
	{
		return $this->sello;
	}

	public function getXML() {
		$this->xml->formatOutput = true;
		$todo = $this->xml->saveXML();
		return $todo;
	}

	public function save($name = null) {
		$this->xml->formatOutput = true;

		if (!file_exists(app_path("cfdi/xml/"))){
			mkdir(app_path("cfdi/xml/"), 0777, true);
		}

		$save = $this->xml->save(app_path("cfdi/xml/{$name}.xml"));
		
		if ( ! $save) {
			throw new Exception("Ocurrio un error al guardar.", 1);
		}
		
	}

	// {{{ Funcion que carga los atributos a la etiqueta XML
	public function setAttr($r, $attr) {
		foreach ($attr as $key => $val) {
		    $val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5a y 5c
		    $val = trim($val);                           // Regla 5b
		    if (strlen($val)>0) {   // Regla 6
		        $val = utf8_encode(str_replace("|","/",$val)); // Regla 1
		        $r->setAttribute($key,$val);
		    }
		}
	}

}
