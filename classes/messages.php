<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */

class Messages {
	/* connection errors */
	const DB_ERROR_CONNECT = array(
			'code' => 50,
			'message' => "No se puede establecer la conexion con el servidor REST"
	);
	
	const REST_ERROR_CONNECT = array(
					'code' => 90, 
					'message' => "No se puede establecer la conexion con el servidor REST"
	);
	
	const REST_ERROR_TIMEOUT = array(
			'code' => 91, 
			'message' => "Timeout - La Operacion REST a exedido el tiempo de ejecución"
	);
	
	/* api errors */
	const INTERNAL_RESULT_ERROR = array(
			'code' => 01,
			'message' => "Internal API Error"
	);
	
	const NULL_RESULT_ERROR = array(
			'code' => 02,
			'message' => "API Call returned NULL value"
	);
	
	const RESULT_ERROR = array(
			'code' => 03,
			'message' => "API Call returned ERROR message"
	);
	
	const BAD_LOGiN_CREDENTIALS = array(
			'code' => 10,
			'message' => "Login attempt failed please check the username and password"
	);
	
	const ACCESS_DENIED_ERROR = array(
			'code' => 40,
			'message' => "Access Denied. User do not have access"
	);
	
	/* constants */
	const LOG_FILE_START 	= "WISE CRM INTEGRATION APPLICATION Logging Started...";
	const BAD_PARAM_FORMAT 	= "El formato de los datos es incorrecto";
	const MISSING_PARAM 	= "Faltan Datos. Por favor inténtelo nuevamente";
	const MISSING_MODULE 	= "No se puede determinar el Módulo. Por favor inténtelo nuevamente";
	const MISSING_APICALL 	= "No se puede determinar la Función. Por favor inténtelo nuevamente";
	const MISSING_CLIENT 	= "No se puede determinar el Cliente. Por favor inténtelo nuevamente";
	const OP_SUCCESS 		= "¡Gracias, los datos se han enviado con éxito!";
	const OP_FAILURE 		= "¡Se ha producido un fallo. Por favor inténtelo nuevamente!";
	const LOGIN_FAIL		= "¡Se ha producido un fallo interno en la aplicación! Los datos no se pueden enviar!";
	const BAD_CONFIG		= "¡La Aplicación no se encuentra correctamente configurada!";
	
	/* HTTP constants */
	const HTTP_OK = array(
			'code' => 200,
			'message' => "OK"
	);
	
	const HTTP_BAD_REQUEST = array(
			'code' => 400,
			'message' => "Bad Request"
	);
	
	const HTTP_NOT_FOUND = array(
			'code' => 404,
			'message' => "Not Found"
	);
		
	const HTTP_REQUEST_TIMEOUT = array(
			'code' => 408,
			'message' => "Request Timeout"
	);
	
	

}
?>