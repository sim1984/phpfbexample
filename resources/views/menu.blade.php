<nav class="navbar main">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".main-collapse">
            <span class="sr-only"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <div class="collapse navbar-collapse main-collapse">
        <ul class="nav nav-tabs">
            <li @if (Request::is('customer*')) class="active"@endif>{!! link_to("customers", "Заказчики") !!}</li>
            <li @if (Request::is('product*')) class="active"@endif>{!! link_to("products", "Товары") !!}</li>
            <li @if (Request::is('invoice*')) class="active"@endif>{!! link_to("invoices", "Счёт фактуры") !!}</li>
        </ul>
    </div>
</nav>
