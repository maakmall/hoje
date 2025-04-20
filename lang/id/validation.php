<?php

return [
    'required' => ':Attribute harus diisi',
    'unique' => ':Attribute sudah digunakan',
    'min' => [
        'string' => ':Attribute harus memiliki setidaknya :min karakter',
        'numeric' => ':Attribute harus lebih besar dari atau sama dengan :min',
    ],
    'max' => [
        'string' => ':Attribute tidak boleh lebih dari :max karakter',
        'numeric' => ':Attribute tidak boleh lebih besar dari :max',
    ],
    'email' => ':Attribute harus berupa alamat email yang valid',
    'numeric' => ':Attribute harus berupa angka',
    'after_or_equal' => ':Attribute harus setelah :date',
];