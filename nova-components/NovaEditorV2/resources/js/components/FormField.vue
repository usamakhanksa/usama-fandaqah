<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <vue-editor
                ref="editor"
                :style="{ direction: 'ltr'}"
                :id="field.name"
                type="text"
                v-model="value"
                :editor-toolbar="customToolbar"
            ></vue-editor>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { VueEditor } from "vue2-editor";

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    components: {
        VueEditor,
    },
    data() {
        return {
            customToolbar: [
                    [{ size: ["small", false, "large", "huge"] }],
                    ["bold", "italic", "underline"],
                    [{ list: "ordered" }, { list: "bullet" }] ,
                    [
                        {
                            align: ""
                            }, {
                                align: "center"
                            }, {
                                align: "right"
                            }, {
                                align: "justify"
                            }
                    ]
                ],
            dir: 'rtl'
        }
    },
    methods: {
      
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || ''
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },
    },
    mounted() {
        const translations = {
    'small': Nova.app.__('Small'),
    '': Nova.app.__('Normal'), // Correct mapping
    'large': Nova.app.__('Large'),
    'huge': Nova.app.__('Huge'),
  };

  const style = document.createElement('style');
  style.type = 'text/css';

  let css = '';
  for (const [value, label] of Object.entries(translations)) {
    const selectorValue = value === '' ? '' : `[data-value="${value}"]`;
    css += `.ql-snow .ql-picker.ql-size .ql-picker-item${selectorValue}::before { content: "${label}" !important; }\n`;
    css += `.ql-snow .ql-picker.ql-size .ql-picker-label${selectorValue}::before { content: "${label}" !important; }\n`;
  }

  style.appendChild(document.createTextNode(css));
  document.head.appendChild(style);
        if (Nova.config.local != 'ar') {
            this.dir = 'ltr';
        }else {
            // this.$refs.editor.quill.format('direction', 'rtl');
            // this.$refs.editor.quill.format('align', 'right');
            // editor.format('direction', 'rtl');
            // editor.format('align', 'right');
        }
    }
}
</script>

<style scoped>
    .ql-editor {
        direction: rtl;
        text-align: right;
    }

    .ql-size .ql-picker{
        direction: ltr !important;
    }
</style>
