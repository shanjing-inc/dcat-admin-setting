<div class="{{$viewClass['form-group']}}">
    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <div {!! $attributes !!} style="width: 100%; height: 100%;">
            <div id="left-{{$column}}" style="width: 100%; height: 100%;"></div>
            <input type="hidden" name="{{$name}}" value="{{ old($column, $value) }}" />
        </div>
        @include('admin::form.help-block')
    </div>
</div>

<!-- script标签加上 "init" 属性后会自动使用 Dcat.init() 方法动态监听元素生成 -->
<script require="@shanjingJsoneditor" init="{!! $selector !!}">
    var container = document.getElementById('left-{{$column}}');
    const options_{{$column}} = {
        mode: 'tree',
        language:'zh',
        modes: ['code', 'tree', 'form', 'text',  'view', 'preview'], // allowed modes
        onError: function (err) {
            alert(err.toString())
        },
        onModeChange: function (newMode, oldMode) {
            console.log('Mode switched from', oldMode, 'to', newMode)
        },
        onChangeText: function(jsonString){
            $("input[name='{{$name}}']").val(JSON.stringify(window['editor_{{$column}}'].get()));
        }
    }
    var json = {!! $value !!};
    var clock = setInterval(function () {
      if (JSONEditor) {
        window['editor_{{$column}}'] = new JSONEditor(container, options_{{$column}}, json)
        clearInterval(clock);
      }
    }, 200);

    $('button[type="submit"]').click(function() {
        var json = window['editor_{{$column}}'].get()
        $("input[name='{{$name}}']").val(JSON.stringify(json))
    })
</script>
