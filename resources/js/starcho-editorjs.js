import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Paragraph from '@editorjs/paragraph';
import Quote from '@editorjs/quote';
import CodeTool from '@editorjs/code';
import Delimiter from '@editorjs/delimiter';
import ImageTool from '@editorjs/image';
import Embed from '@editorjs/embed';
import Table from '@editorjs/table';
import Warning from '@editorjs/warning';
import InlineCode from '@editorjs/inline-code';
import Marker from '@editorjs/marker';
import { StarchoHtmlEditor } from './starcho-html-editor';

Object.assign(window, {
    EditorJS,
    Header,
    List,
    Paragraph,
    Quote,
    CodeTool,
    Delimiter,
    ImageTool,
    Embed,
    Table,
    Warning,
    InlineCode,
    Marker,
    StarchoHtmlEditor,
});

window.StarchoEditorJsReady = true;
window.dispatchEvent(new CustomEvent('starcho-editorjs:ready'));
