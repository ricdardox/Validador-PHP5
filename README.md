# Validador-PHP5


# Ejemplo:

namespace Validation;

require '../../vendor/autoload.php';

class Test {

    public static function init() {
        $rules = [
            'carne' => ['value' => '', 'rules' => ['required', 'numeric']],
            'fechaIngreso' => ['value' => '', 'rules' => ['required', 'date']],
            'rangoSalarial' => ['value' => '', 'rules' => ['required']],
            'tipoAfiliado' => ['value' => '', 'rules' => ['required', 'in:Cotizante,Beneficiario']],
        ];

        $validacion = Validation::make($rules);

        if ($validacion->passes()) {
            echo 'La información cumple con las reglas de validación';
        } else {
            echo 'La información no cumple con las reglas de validación<br>';
            print_r($validacion->messages());
        }
    }

}

Test::init();
