
<li class="nav-item">
    <a href="{{ route('giftCards.index') }}" class="nav-link {{ Request::is('giftCards*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Gift Cards</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('qRSessions.index') }}" class="nav-link {{ Request::is('qRSessions*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>QR Sessions</p>
    </a>
</li>

