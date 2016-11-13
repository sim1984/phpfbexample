<?php

namespace App;

use Firebird\Eloquent\Model;


class Customer extends Model
{
    /**
     * таблица связанная с моделью
     *
     * @var string
     */
    protected $table = 'CUSTOMER';
    
    /**
     * Первичный ключ модели
     *
     * @var string
     */
    protected $primaryKey = 'CUSTOMER_ID';    
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;  
    
    /**
     * имя последовательности для генерации первичного ключа
     * @var string 
     */
    protected $sequence = 'GEN_CUSTOMER_ID';
     
}

