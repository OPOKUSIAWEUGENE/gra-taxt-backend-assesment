<?php

namespace App\Http\Requests;

use App\Traits\Common;
use App\Traits\Errors;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GRAGrossRequest extends FormRequest
{

    use Common;
    use Errors;
    /**
     * validation
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error(
                self::$badRequest,
                $validator->errors()->all(),
                400
            )
        );
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'allowance'=>['required','numeric'],
            'net_salary'=>['required','numeric'],
          

        ];
    }
}
