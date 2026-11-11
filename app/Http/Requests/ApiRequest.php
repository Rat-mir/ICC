<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ApiRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function messages(): array { return [
        'required'=>'Поле :attribute обязательно.',
        'required_without'=>'Укажите :attribute.',
        'string'=>'Поле :attribute должно быть строкой.',
        'email'=>'Некорректная электронная почта.',
        'unique'=>'Такое значение :attribute уже используется.',
        'confirmed'=>'Подтверждение пароля не совпадает.',
        'min.string'=>'Минимальная длина :attribute — :min символов.',
        'min.array'=>'Выберите хотя бы :min элемент.',
        'min.numeric'=>'Поле :attribute должно быть не меньше :min.',
        'max.string'=>'Максимальная длина :attribute — :max символов.',
        'max.file'=>'Размер файла не должен превышать :max КБ.',
        'max.array'=>'Слишком много элементов (максимум :max).',
        'image'=>'Файл должен быть изображением.',
        'file'=>'Загрузите файл.',
        'mimes'=>'Допускается только PNG.',
        'mimetypes'=>'Недопустимый MIME-тип файла.',
        'dimensions'=>'Изображение должно быть от 16×16 до 2048×2048 пикселей.',
        'exists'=>'Связанная запись не найдена.',
        'enum'=>'Недопустимое значение :attribute.',
        'in'=>'Недопустимое значение :attribute.',
        'integer'=>'Поле :attribute должно быть целым числом.',
        'boolean'=>'Поле :attribute должно быть логическим.',
        'array'=>'Поле :attribute должно быть массивом.',
        'distinct'=>'Элементы не должны повторяться.',
        'between'=>'Поле :attribute должно быть в диапазоне :min–:max.',
        'date'=>'Укажите корректную дату.',
        'after_or_equal'=>'Конечная дата должна быть не раньше начальной.',
        'prohibited'=>'Изменение :attribute запрещено.',
    ]; }
}
