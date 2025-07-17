<div class="{{$viewClass['form-group']}}">
    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <div {!! $attributes !!} style="width: 100%; min-height: 300px;">
            <div id="left-{{$column}}" style="width: 100%; min-height: 300px;"></div>
            <input type="hidden" name="{{$name}}" value="{{ old($column, $value) }}" />
        </div>
        @include('admin::form.help-block')
    </div>
</div>

<!-- script标签加上 "init" 属性后会自动使用 Dcat.init() 方法动态监听元素生成 -->
<script require="@shanjingJsoneditor" init="{!! $selector !!}">
    var container = document.getElementById('left-{{$column}}');

    // 计算编辑器自适应高度的函数
    function calculateEditorHeight(jsonData, mode) {
        if (mode === 'tree' || mode === 'form' || mode === 'view' || mode === 'preview') {
            // 对于这些模式，JSONEditor 会自动调整高度
            return null;
        }

        // 对于 code 和 text 模式，计算所需高度
        const jsonString = JSON.stringify(jsonData, null, 2);
        const lineCount = jsonString.split('\n').length;
        const lineHeight = 16; // Ace editor 的行高
        const padding = 40; // 编辑器的内边距和工具栏
        const minHeight = 300; // 最小高度
        const maxHeight = 600; // 最大高度，避免过高

        const calculatedHeight = (lineCount * lineHeight) + padding;
        return Math.min(Math.max(calculatedHeight, minHeight), maxHeight);
    }

    // 动态调整编辑器高度
    function adjustEditorHeight(editor, jsonData, mode) {
        const height = calculateEditorHeight(jsonData, mode);
        if (height) {
            container.style.height = height + 'px';
            // 如果编辑器已经初始化，重新调整大小
            if (editor && editor.aceEditor) {
                setTimeout(() => {
                    editor.aceEditor.resize();
                }, 100);
            }
        } else {
            // 对于自动调整高度的模式，移除固定高度
            container.style.height = 'auto';
            container.style.minHeight = '300px';
        }
    }

    const options_{{$column}} = {
        mode: 'code',
        language:'zh',
        modes: ['code', 'tree', 'form', 'text', 'view', 'preview'], // allowed modes
        onError: function (err) {
            alert(err.toString())
        },
        onModeChange: function (newMode, oldMode) {
            console.log('Mode switched from', oldMode, 'to', newMode);
            // 模式切换时重新调整高度
            const currentData = window['editor_{{$column}}'].get();
            adjustEditorHeight(window['editor_{{$column}}'], currentData, newMode);
        },
        onChangeText: function(jsonString){
            $("input[name='{{$name}}']").val(JSON.stringify(window['editor_{{$column}}'].get()));
            // 内容变化时重新调整高度（仅对 code 和 text 模式）
            const currentMode = window['editor_{{$column}}'].getMode();
            if (currentMode === 'code' || currentMode === 'text') {
                try {
                    const currentData = JSON.parse(jsonString);
                    adjustEditorHeight(window['editor_{{$column}}'], currentData, currentMode);
                } catch (e) {
                    // JSON 解析失败时使用字符串长度估算
                    const lineCount = jsonString.split('\n').length;
                    const height = Math.min(Math.max((lineCount * 16) + 40, 300), 800);
                    container.style.height = height + 'px';
                    if (window['editor_{{$column}}'].aceEditor) {
                        setTimeout(() => {
                            window['editor_{{$column}}'].aceEditor.resize();
                        }, 100);
                    }
                }
            }
        }
    }

    var json = {!! $value !!};

    var clock = setInterval(function () {
        if (JSONEditor) {
            // 初始化编辑器前先调整容器高度
            adjustEditorHeight(null, json, options_{{$column}}.mode);

            window['editor_{{$column}}'] = new JSONEditor(container, options_{{$column}}, json);

            // 编辑器初始化完成后再次调整高度
            setTimeout(() => {
                adjustEditorHeight(window['editor_{{$column}}'], json, options_{{$column}}.mode);
            }, 500);

            clearInterval(clock);
        }
    }, 200);

    $('button[type="submit"]').click(function() {
        var json = window['editor_{{$column}}'].get()
        $("input[name='{{$name}}']").val(JSON.stringify(json))
    })
</script>
