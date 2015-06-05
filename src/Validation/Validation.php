<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Esta clase permite validar campos de forma organizada 
 * @author Ricardo Jose Montes Rodriguez <ricardomontesrodriguez@gmail.com>
 */

namespace Validation;

class Validation {

    private $langDefault;
    private $langData;
    private $passes;
    private $failed;
    private $messages;

    public function __construct() {
        $this->langDefault = 'Es';
        $this->langData = [];
        $this->loadLang();
        $this->passes = false;
        $this->failed = false;
        $this->messages = [];
    }

    /**
     * Carga en el lenguaje que utilizaŕa el validador para mostrar los mensajes
     * @author Ricardo Jose Montes Rodriguez <ricardomontesrodriguez@gmail.com>
     */
    public function loadLang() {
        try {
            $file = 'Lang/ValidationLang' . $this->langDefault . '.php';
            $this->langData = require_once $file;
        } catch (Exception $ex) {
            
        }
    }

    /**
     * Devuelve el lenguaje que esta usando el validador por defecto
     * @return string
     */
    function getLangDefault() {
        return $this->langDefault;
    }

    /**
     * Devuelve un arreglo con todqas las etiuquetas del lenguaje
     * @return array
     */
    function getLangData() {
        return $this->langData;
    }

    /**
     * Permite cambiar el lenguaje que usará el validador por defecto
     * @param string $langDefault
     */
    function setLangDefault($langDefault) {
        $this->langDefault = $langDefault;
        $this->loadLang();
    }

    /**
     * Permite cambiar el arreglo de etiquetas del lenguaje
     * @param array $langData
     */
    function setLangData($langData) {
        $this->langData = $langData;
    }

    /**
     * Permite saber si los datos no pasaron la validación
     * @return boolean verdadero si ha los datos no han pasado la validación
     */
    function failed() {
        return $this->failed;
    }

    /**
     * Permite saber si los datos  pasaron la validación
     * @return boolean verdadero si ha los datos  han pasado la validación
     */
    function passes() {
        return $this->passes;
    }

    /**
     * Devuelve todos los mensajes de error generados cuando los datos no pasan la validación
     * @return array arreglo asociativo con los mensajes de error donde el key es el campo
     */
    function messages() {
        return $this->messages;
    }

    /**
     * 
     * @return string
     */
    function messagesText() {
        $textResult = "";
        foreach ($this->messages as $key => $value) {
            foreach ($value as $text) {
                $textResult.=$text . '. ';
            }
        }
        return $textResult;
    }

    function setPasses($passes) {
        $this->passes = $passes;
    }

    function setMessages($messages) {
        $this->messages = $messages;
    }

    function setFailed($failed) {
        $this->failed = $failed;
    }

    /*
     * INICIO DE LOS METODOS DE VALIDACIÓN
     * LOS METODOS EXPUESTO AQUI DEVUELVEN VERDADERO SI LA VALIDACIÓN QUE SE ESTA APLICANDO 
     * ES SE CUMPLE EJEMPLO: PARA LOS CAMPOS REQUERIDOS SI EN REALIDAD EL CAMPO ESTA VACIO ESTO 
     * DEVOLVERÁ VERDADERO.
     * 
     */

    public function in($key, $valor, $extra = []) {
        return !in_array($valor, $extra);
    }

    public function required($key, $valor) {
        return empty($valor);
    }

    public function numeric($key, $valor) {
        return !is_numeric($valor);
    }

    public function date($key, $value, $format = "") {
        $separator_type = array(
            "/",
            "-",
            "."
        );
        foreach ($separator_type as $separator) {
            $find = stripos($value, $separator);
            if ($find <> false) {
                $separator_used = $separator;
            }
        }
        $input_array = explode($separator_used, $value);
        if ($format == "mdy") {
            return !checkdate($input_array[0], $input_array[1], $input_array[2]);
        } elseif ($format == "ymd") {
            return !checkdate($input_array[1], $input_array[2], $input_array[0]);
        } else {
            return !checkdate($input_array[1], $input_array[0], $input_array[2]);
        }
        $input_array = array();
    }

    /*
     * FIN DE LOS METODOS DE VALIDACIÓN
     * 
     */

    /**
     * Se encarga de ejecutar la validación de los datos dependiendo de las reglasd configurada
     * @param type $rules es un arreglo asociativo con las reglas que se le aplicarán a los datos
     * @param type $lang es el lenguaje en el que se devolverán los datos
     * @return \Validation\Validation retorna un objeto con la infación generada por el validador
     */
    public static function make($rules, $lang = '') {
        $validation = new Validation();
        if ($lang != '') {
            $validation->setLangDefault($lang);
        }
        $messages = [];
        $validation->setPasses(true);
        foreach ($rules as $key => $value) {
            foreach ($value['rules'] as $methodIn) {
                $methodArray = explode(':', $methodIn);
                if (count($methodArray) > 0) {
                    $method = $methodArray[0];
                    $extra = explode(',', $methodArray[1]);
                } else {
                    $method = $methodArray;
                }
                if (method_exists($validation, $method)) {
                    if ($validation->$method($key, $value['value'], $extra)) {
                        $messages[$key][] = str_replace(':values', implode(',', $extra), str_replace(':field', $key, $validation->getLangData()[$method]));
                        $validation->setPasses(false);
                        $validation->setFailed(true);
                    }
                }
            }
        }
        $validation->setMessages($messages);
        return $validation;
    }

}
