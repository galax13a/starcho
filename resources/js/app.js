import '../../vendor/power-components/livewire-powergrid/dist/powergrid.js';
import Notiflix from 'notiflix';

window.starchoDelete = function (recordId, name, livewireEvent, componentName) {
    Notiflix.Confirm.show(
        'Confirmar eliminación',
        '¿Eliminar "' + name + '"? Esta acción no se puede deshacer.',
        'Sí, eliminar',
        'Cancelar',
        function () {
            Livewire.dispatchTo(componentName, livewireEvent, { id: recordId });
        },
        function () {},
        {
            backOverlayColor: 'rgba(0,0,10,0.5)',
            cssAnimationStyle: 'zoom',
            textColor: '#fff',
            backgroundColor: '#520281',
            cssAnimation: true,
            messageColor: '#56c080',
            okButtonBackground: '#f7086b',
            onReady: function () {
                var btn = document.querySelector('#NXConfirmButtonOk');
                if (btn) { btn.tabIndex = 0; btn.focus(); }
            },
        }
    );
};
