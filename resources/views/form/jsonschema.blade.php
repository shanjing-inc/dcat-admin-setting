<div class="{{$viewClass['form-group']}}">
    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <div {!! $attributes !!} style="width: 100%; min-height: 500px;">
            <!-- 标签页导航 -->
            <ul class="nav nav-tabs" id="jsonschema-tabs-{{$column}}" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="form-tab-{{$column}}" data-toggle="tab" href="#form-{{$column}}" role="tab" aria-controls="form-{{$column}}" aria-selected="true">表单编辑</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="schema-tab-{{$column}}" data-toggle="tab" href="#schema-{{$column}}" role="tab" aria-controls="schema-{{$column}}" aria-selected="false">Schema编辑</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="preview-tab-{{$column}}" data-toggle="tab" href="#preview-{{$column}}" role="tab" aria-controls="preview-{{$column}}" aria-selected="false">数据预览</a>
                </li>
            </ul>

            <!-- 标签页内容 -->
            <div class="tab-content" id="jsonschema-tabContent-{{$column}}">
                <!-- 表单编辑标签页 -->
                <div class="tab-pane fade show active" id="form-{{$column}}" role="tabpanel" aria-labelledby="form-tab-{{$column}}">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">基于Schema的表单</h5>
                            <small class="text-muted">根据JSON Schema自动生成的表单，填写的数据将保存到系统设置中</small>
                        </div>
                        <div class="card-body">
                            <div id="form-editor-{{$column}}" style="min-height: 400px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Schema编辑标签页 -->
                <div class="tab-pane fade" id="schema-{{$column}}" role="tabpanel" aria-labelledby="schema-tab-{{$column}}">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">JSON Schema编辑器</h5>
                            <small class="text-muted">💡 提示：可用 <a href="https://json.ophir.dev/" target="_blank">https://json.ophir.dev</a> 生成 json schema，为键值字段生成表单样式，方便编辑</small>
                        </div>
                        <div class="card-body">


                            <!-- JSON Schema 文本编辑器 -->
                            <div class="mb-3">
                                <label class="form-label">JSON Schema:</label>
                                <textarea id="schema-json-{{$column}}" class="form-control" style="height: 400px; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 13px; line-height: 1.4; resize: vertical;" placeholder="在此输入JSON Schema，编辑完成后点击'手动应用Schema'按钮..."></textarea>
                            </div>

                            <!-- 操作按钮 -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" class="btn btn-primary" id="apply-schema-{{$column}}">应用Schema</button>
                                    <button type="button" class="btn btn-secondary" id="reset-schema-{{$column}}">重置为默认</button>
                                    <button type="button" class="btn btn-info" id="format-json-{{$column}}">格式化JSON</button>
                                    <button type="button" class="btn btn-danger" id="clean-schema-{{$column}}">清空Schema</button>
                                </div>
                                <small class="text-muted">💡 提示：编辑完JSON Schema后，点击"应用Schema"按钮更新表单；"清空Schema"后并保存，恢复默认JSON编辑器；</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 数据预览标签页 -->
                <div class="tab-pane fade" id="preview-{{$column}}" role="tabpanel" aria-labelledby="preview-tab-{{$column}}">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">数据预览</h5>
                            <small class="text-muted">查看当前表单数据和Schema的JSON格式</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>表单数据:</label>
                                    <pre id="data-preview-{{$column}}" style="height: 400px; overflow: auto; background: #f8f9fa; padding: 15px; border: 1px solid #ddd;"></pre>
                                </div>
                                <div class="col-md-6">
                                    <label>Schema配置:</label>
                                    <pre id="schema-preview-{{$column}}" style="height: 400px; overflow: auto; background: #f8f9fa; padding: 15px; border: 1px solid #ddd;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 隐藏的表单字段 -->
            <input type="hidden" name="{{$name}}" id="hidden-input-{{$column}}" value="{{ old($column, $value) }}" />
        </div>
        @include('admin::form.help-block')
    </div>
</div>

<!-- script标签加上 "init" 属性后会自动使用 Dcat.init() 方法动态监听元素生成 -->
<script require="@shanjingJsonSchema" init="{!! $selector !!}">
(function() {
    const columnId = '{{$column}}';
    const hiddenInput = document.getElementById('hidden-input-' + columnId);

    // 获取初始值
    let initialValue;
    try {
        initialValue = JSON.parse(hiddenInput.value || '{}');
    } catch (e) {
        initialValue = {};
    }

    const defaultSchema = initialValue.schema || {};
    const defaultValue  = initialValue.data || {};
    // 当前配置
    let currentConfig = {
        schema: defaultSchema,
        data: defaultValue
    };



    // 表单编辑器实例
    let currentFormEditor = null;

    // 验证状态跟踪
    let hasValidationErrors = false;

    // 验证表单编辑器
    function validateFormEditor() {
        if (currentFormEditor) {
            const errors = currentFormEditor.validate();
            hasValidationErrors = errors.length > 0;

            if (hasValidationErrors) {
                currentFormEditor.showValidationErrors();
            }

            return !hasValidationErrors;
        }
        hasValidationErrors = false;
        return true;
    }

    // 更新隐藏字段
    function updateHiddenInput() {
        const hiddenInput = document.getElementById('hidden-input-' + columnId);
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify({
                schema: currentConfig.schema,
                data: currentConfig.data
            });
        }
    }

    // 初始化表单编辑器
    function initFormEditor() {
        const formContainer = document.getElementById('form-editor-' + columnId);

        if (currentFormEditor) {
            currentFormEditor.destroy();
        }
        if (currentConfig.schema && Object.keys(currentConfig.schema).length > 0) {
            currentFormEditor = new JSONEditor(formContainer, {
                schema: currentConfig.schema,
                startval: currentConfig.data,
                iconlib: "fontawesome4",
                theme: 'bootstrap5',
                disable_edit_json: true,
                disable_properties: true,
            });


            // 监听表单变化
            currentFormEditor.on('change', function() {
                currentConfig.data = currentFormEditor.getValue();
                updateHiddenInput();
                // 实时验证
                validateFormEditor();
            });

            currentFormEditor.on('ready',() => {
                // Now the api methods will be available
                validateFormEditor();
            });
        } else {
            formContainer.innerHTML = '<div class="alert alert-info">请先设置JSON Schema</div>';
        }

        // 初始化隐藏字段
        updateHiddenInput();
        initSchemaEditor();
    }

    // 初始化Schema文本编辑器
    function initSchemaEditor() {
        const schemaJsonTextarea = document.getElementById('schema-json-' + columnId);

        // 初始化文本框内容
        schemaJsonTextarea.value = JSON.stringify(currentConfig.schema, null, 2);
    }

    // 应用Schema
    function applySchema() {
        try {
            const schemaJsonTextarea = document.getElementById('schema-json-' + columnId);
            const newSchema = JSON.parse(schemaJsonTextarea.value);

            // 验证Schema基本结构
            if (!newSchema || typeof newSchema !== 'object') {
                throw new Error('Schema必须是一个有效的JSON对象');
            }

            // 验证现有数据是否与新schema兼容
            // if (currentFormEditor) {
            //     try {
            //         var validation = currentFormEditor.validate();
            //         console.log(validation);
            //         if (validation.length > 0) {
            //             console.warn('现有数据与新Schema不兼容，将清空数据');
            //             currentConfig.data = {};
            //         }
            //     } catch (e) {
            //         console.warn('验证数据时出错，将清空数据');
            //         currentConfig.data = {};
            //     }
            // }

            currentConfig.schema = newSchema;
            initFormEditor();
            updateHiddenInput();
            updatePreview();

            // 切换到表单编辑标签页
            document.getElementById('form-tab-' + columnId).click();

            Dcat.success('Schema应用成功！');
        } catch (e) {
            Dcat.error('Schema格式错误：' + e.message);
        }
    }

    // 重置Schema
    function resetSchema() {
        currentConfig.schema = defaultSchema;
        currentConfig.data   = defaultValue;
        initSchemaEditor();
        initFormEditor();
        updateHiddenInput();
        updatePreview();
        Dcat.success('Schema已重置为默认值！');
    }


    // 格式化JSON函数
    function formatJson() {
        const schemaJsonTextarea = document.getElementById('schema-json-' + columnId);
        try {
            const parsed = JSON.parse(schemaJsonTextarea.value);
            schemaJsonTextarea.value = JSON.stringify(parsed, null, 2);
            Dcat.success('JSON格式化成功！');
        } catch (e) {
            Dcat.error('JSON格式错误，无法格式化：' + e.message);
        }
    }

    function cleanSchema() {
        const schemaJsonTextarea = document.getElementById('schema-json-' + columnId);

        currentConfig.schema = {};
        schemaJsonTextarea.value = '{}';
        initFormEditor();
        updateHiddenInput();
        updatePreview();

        // 切换到表单编辑标签页
        document.getElementById('form-tab-' + columnId).click();

        Dcat.success('Schema 清空成功！');
    }

    // 绑定事件
    document.getElementById('apply-schema-' + columnId).addEventListener('click', applySchema);
    document.getElementById('reset-schema-' + columnId).addEventListener('click', resetSchema);
    document.getElementById('format-json-' + columnId).addEventListener('click', formatJson);
    document.getElementById('clean-schema-' + columnId).addEventListener('click', cleanSchema);

    // 数据预览更新
    function updatePreview() {
        const dataPreview = document.getElementById('data-preview-' + columnId);
        const schemaPreview = document.getElementById('schema-preview-' + columnId);

        if (dataPreview) {
            dataPreview.textContent = JSON.stringify(currentConfig.data, null, 2);
        }

        if (schemaPreview) {
            schemaPreview.textContent = JSON.stringify(currentConfig.schema, null, 2);
        }
    }

    // 预览标签页切换事件
    document.getElementById('preview-tab-' + columnId).addEventListener('click', function() {
        updatePreview();
    });

    // 表单提交拦截
    function setupFormSubmissionControl() {
        const button = document.querySelector('.submit');
        // 拦截提交按钮
        button._validationHandler = function(e) {
            if (currentFormEditor && hasValidationErrors) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                Dcat.error('表单验证失败，请检查并修正错误后再提交！');
                return false;
            }
        };

        button.addEventListener('click', button._validationHandler, true);
    }

    // 初始化
     initFormEditor();
     updatePreview();

     // 延迟设置表单提交控制，确保 DOM 完全加载
     setTimeout(function() {
         setupFormSubmissionControl();
     }, 1000);

})();
</script>
