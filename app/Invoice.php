<?php

namespace App;

use Firebird\Eloquent\Model;

class Invoice extends Model {

    /**
     * таблица связанная с моделью
     *
     * @var string
     */
    protected $table = 'INVOICE';

    /**
     * Первичный ключ модели
     *
     * @var string
     */
    protected $primaryKey = 'INVOICE_ID';

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
    protected $sequence = 'GEN_INVOICE_ID';

    /**
     * Заказачик
     * @return \App\Customer
     */
    public function customer() {
        return $this->belongsTo('App\Customer', 'CUSTOMER_ID');
    }

    /**
     * Позиции счёт фактуры
     * @return \AppInvoiceLine[]
     */
    public function lines() {
        return $this->hasMany('App\InvoiceLine', 'INVOICE_ID');
    }
    
    /**
     * Оплата 
     */
    public function pay() {
        $connection = $this->getConnection();

        $attributes = $this->attributes;

        $connection->executeProcedure('SP_PAY_FOR_INOVICE', [$attributes['INVOICE_ID']]);
    }
 
}
