@props([
	'modalName',
	'submitMethod',
	'loadingTarget' => null,
	'title',
	'fileModel' => 'importFile',
	'accept' => '.xlsx,.xls,.csv',
])

@php
	$target = $loadingTarget ?: $submitMethod;
@endphp

<ui-modal focusable="focusable" data-flux-modal="" data-open="">
	<dialog
		wire:ignore.self
		class="p-6 [:where(&)]:max-w-xl [:where(&)]:min-w-xs shadow-lg rounded-xl bg-white dark:bg-zinc-800 ring ring-black/5 dark:ring-zinc-700 shadow-lg rounded-xl md:w-96"
		data-modal="{{ $modalName }}"
		x-data="fluxModal('{{ $modalName }}')"
		x-on:modal-show.document="handleShow($event)"
		x-on:modal-close.document="handleClose($event)"
	>
		<div data-flux-focus-placeholder data-appended tabindex="0" style="display: none"></div>

		<form wire:submit="{{ $submitMethod }}" class="space-y-5">
			<div class="font-medium [:where(&)]:text-zinc-800 [:where(&)]:dark:text-white text-base [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2" data-flux-heading="">{{ $title }}</div>

			<ui-field class="min-w-0 [&:not(:has([data-flux-field])):has([data-flux-control][disabled])>&[data-flux-label]]:opacity-50 [&:has(>[data-flux-radio-group][disabled])>&[data-flux-label]]:opacity-50 [&:has(>[data-flux-checkbox-group][disabled])>&[data-flux-label]]:opacity-50 block *:data-flux-label:mb-3 [&>[data-flux-label]:has(+[data-flux-description])]:mb-2 [&>[data-flux-label]+[data-flux-description]]:mt-0 [&>[data-flux-label]+[data-flux-description]]:mb-3 [&>*:not([data-flux-label])+[data-flux-description]]:mt-3" data-flux-field="">
				<ui-label class="inline-flex items-center text-sm font-medium [:where(&)]:text-zinc-800 [:where(&)]:dark:text-white [&:has([data-flux-label-trailing])]:flex" data-flux-label="">{{ __('admin_ui.common.file') }}</ui-label>

				<div class="relative block group/input" data-flux-input="">
					<input
						type="file"
						wire:model="{{ $fileModel }}"
						accept="{{ $accept }}"
						class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm py-2 h-10 leading-[1.375rem] ps-3 pe-3 bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5 data-invalid:shadow-none data-invalid:border-red-500 dark:data-invalid:border-red-500"
						data-flux-control
						name="{{ $fileModel }}"
					/>
				</div>

				@error($fileModel)
					<div class="mt-3 text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</div>
				@enderror
			</ui-field>

			<div class="flex justify-end gap-2 pt-1">
				<ui-close data-flux-modal-close>
					<button
						type="button"
						class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none justify-center h-10 text-sm rounded-lg ps-4 pe-4 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-800 dark:text-white"
						data-flux-button
					>
						{{ __('admin_ui.common.cancel') }}
					</button>
				</ui-close>

				<button
					type="submit"
					class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none justify-center h-10 text-sm rounded-lg ps-4 pe-4 inline-flex bg-[var(--color-accent)] hover:bg-[color-mix(in_oklab,_var(--color-accent),_transparent_10%)] text-[var(--color-accent-foreground)] border border-black/10 dark:border-0"
					data-flux-button
					wire:loading.attr="disabled"
				>
					<span>
						<span wire:loading.remove wire:target="{{ $target }}">{{ __('admin_ui.common.import') }}</span>
						<span wire:loading wire:target="{{ $target }}">{{ __('admin_ui.common.importing') }}</span>
					</span>
				</button>
			</div>
		</form>

		<div class="absolute top-0 end-0 mt-4 me-4">
			<ui-close data-flux-modal-close>
				<button
					type="button"
					class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none justify-center h-8 text-sm rounded-md w-8 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-400! hover:text-zinc-800!"
					data-flux-button
					aria-label="{{ __('Close') }}"
				>
					<svg class="shrink-0 [:where(&)]:size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
						<path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
					</svg>
				</button>
			</ui-close>
		</div>
	</dialog>
</ui-modal>
