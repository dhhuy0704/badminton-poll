<div class="language-switcher">
    <style>
        .language-switcher {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
            align-items: center;
        }
        .language-switcher form {
            display: inline;
        }
        .language-switcher button {
            background: none;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            color: #666;
            font-weight: bold;
        }
        .language-switcher .active {
            color: #FF8339;
            font-weight: bold;
        }
        .language-switcher .separator {
            margin: 0 5px;
        }
        .language-switcher .current-locale {
            font-size: 10px;
            color: #999;
            display: block;
            text-align: center;
            margin-top: 5px;
        }
    </style>

    @if(app()->getLocale() == 'en')
        <form action="{{ route('language.switch', 'vi') }}" method="GET">
            <button type="submit" class="{{ app()->getLocale() == 'vi' ? 'active' : '' }}">Coi Bằng Tiếng Việt</button>
        </form>
    @else
        <form action="{{ route('language.switch', 'en') }}" method="GET">
            <button type="submit" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">See In English</button>
        </form>
    @endif

</div>
