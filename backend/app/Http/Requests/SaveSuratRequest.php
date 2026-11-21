<?php

namespace App\Http\Requests;

use App\Models\PengajuanSurat;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSuratRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis_pemohon' => ['required', Rule::in(['warga', 'non_warga'])],
            'warga_id' => [
                'exclude_unless:jenis_pemohon,warga',
                'required',
                'integer',
                Rule::exists('warga', 'id')->where('status_aktif', true),
            ],
            'nama_pemohon' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'string', 'max:150'],
            'nik_pemohon' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'regex:/^[0-9]{16}$/'],
            'jenis_kelamin' => ['exclude_unless:jenis_pemohon,non_warga', 'required', Rule::in(['Laki-laki', 'Perempuan'])],
            'tempat_lahir' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'string', 'max:100'],
            'tanggal_lahir' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'date', 'before_or_equal:today'],
            'agama' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'string', 'max:50'],
            'pekerjaan' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'string', 'max:100'],
            'status_perkawinan' => ['exclude_unless:jenis_pemohon,non_warga', 'required', Rule::in(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])],
            'kewarganegaraan' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'string', 'max:80'],
            'alamat_ktp' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'string', 'max:2000'],
            'alamat_domisili' => ['exclude_unless:jenis_pemohon,non_warga', 'required', 'string', 'max:2000'],
            'jenis_surat' => ['required', Rule::in(PengajuanSurat::JENIS_SURAT)],
            'keperluan' => ['required', 'string', 'max:255'],
            'catatan_warga' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
