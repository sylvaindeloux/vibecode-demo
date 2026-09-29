/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

/*
 * Monthly calendar: day states, keyboard grid navigation, day dialog, quick actions,
 * live summary and persistence. Spec: references/calendar.md
 *
 * DOM contract (rendered by <twig:Calendar>):
 *   td.calendar-day[data-calendar-target="day"]
 *     data-date="2026-09-14" data-kind="workday|weekend|holiday|outside"
 *     data-state="empty|full|half|off" data-note="…" data-label="lundi 14 septembre 2026, jour férié : …"
 *     [data-today] [data-has-note] [data-disabled]
 *     > button.calendar-day__button (roving tabindex: exactly one day has tabindex="0")
 *
 * Persistence: POST {urlValue} with JSON {"days":[{"date","state","note"}]} and header
 * X-CSRF-Token. Any 2xx = saved. Otherwise the change is reverted and an error toast is shown.
 * Without urlValue (static examples), changes stay client-side.
 */
const STATES = ['empty', 'full', 'half', 'off'];
const CYCLE = { empty: 'full', full: 'half', half: 'empty', off: 'empty' };
const KEY_TO_STATE = { j: 'full', d: 'half', c: 'off', Delete: 'empty', Backspace: 'empty' };

export default class extends Controller {
    static targets = [
        'day', 'status', 'summaryValue', 'labelTemplate', 'prevLink', 'nextLink',
        'selectionDate', 'selectionState', 'selectionNote', 'selectionEdit',
        'dialog', 'dialogDate', 'dialogNote',
    ];

    static values = {
        url: String,
        csrfToken: String,
        readonly: Boolean,
        labels: Object, // French texts, see <twig:Calendar> (states, units, messages)
    };

    connect() {
        this.locale = document.documentElement.lang || 'fr';
        this.numberFormat = new Intl.NumberFormat(this.locale, { maximumFractionDigits: 1 });
        this.pluralRules = new Intl.PluralRules(this.locale);
        this.selected = this.buttons.find((b) => b.tabIndex === 0)?.closest('td') ?? this.activeDays[0];
        this.refreshSelection();
        this.refreshSummary();
    }

    // ------------------------------------------------------------------ getters
    get activeDays() {
        return this.dayTargets.filter((td) => td.dataset.kind !== 'outside');
    }

    get buttons() {
        return this.activeDays.map((td) => td.querySelector('.calendar-day__button'));
    }

    dayOf(event) {
        return event.target.closest('td.calendar-day');
    }

    // ------------------------------------------------------------------ pointer + keyboard
    cycle(event) {
        const day = this.dayOf(event);
        if (!day || day.dataset.kind === 'outside') return;
        this.select(day);
        this.apply([{ day, state: CYCLE[day.dataset.state] ?? 'full' }]);
    }

    focus(event) {
        const day = this.dayOf(event);
        if (day) this.select(day, false);
    }

    keydown(event) {
        const day = this.dayOf(event);
        if (!day) return;
        const days = this.activeDays;
        const index = days.indexOf(day);
        const row = Array.from(day.parentElement.children).filter((td) => days.includes(td));
        let target = null;

        switch (event.key) {
            case 'ArrowRight': target = days[index + 1]; break;
            case 'ArrowLeft': target = days[index - 1]; break;
            case 'ArrowDown': target = days[index + 7] ?? days.at(-1); break;
            case 'ArrowUp': target = days[index - 7] ?? days[0]; break;
            case 'Home': target = event.ctrlKey || event.metaKey ? days[0] : row[0]; break;
            case 'End': target = event.ctrlKey || event.metaKey ? days.at(-1) : row.at(-1); break;
            case 'PageUp': if (this.hasPrevLinkTarget) { event.preventDefault(); this.prevLinkTarget.click(); } return;
            case 'PageDown': if (this.hasNextLinkTarget) { event.preventDefault(); this.nextLinkTarget.click(); } return;
            default: {
                if (event.ctrlKey || event.metaKey || event.altKey) return;
                const key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
                if (key === 'n') {
                    event.preventDefault();
                    this.select(day);
                    this.openDialog();
                    return;
                }
                if (KEY_TO_STATE[key]) {
                    event.preventDefault();
                    this.apply([{ day, state: KEY_TO_STATE[key] }]);
                }
                return;
            }
        }

        event.preventDefault();
        if (target) this.select(target, true);
    }

    // ------------------------------------------------------------------ selection (roving tabindex)
    select(day, moveFocus = false) {
        this.buttons.forEach((b) => { b.tabIndex = -1; });
        const button = day.querySelector('.calendar-day__button');
        button.tabIndex = 0;
        if (moveFocus) button.focus();
        this.selected = day;
        this.refreshSelection();
    }

    refreshSelection() {
        const day = this.selected;
        if (!day) return;
        if (this.hasSelectionDateTarget) this.selectionDateTarget.textContent = day.dataset.label;
        if (this.hasSelectionStateTarget) this.selectionStateTarget.textContent = this.stateLabel(day.dataset.state);
        if (this.hasSelectionNoteTarget) this.selectionNoteTarget.textContent = day.dataset.note ?? '';
    }

    // ------------------------------------------------------------------ day dialog (state + note)
    openDialog() {
        if (!this.hasDialogTarget || !this.selected || this.readonlyValue) return;
        const day = this.selected;
        this.dialogDateTarget.textContent = day.dataset.label;
        this.dialogTarget.querySelectorAll('input[name="day-state"]').forEach((radio) => {
            radio.checked = radio.value === day.dataset.state;
        });
        this.dialogNoteTarget.value = day.dataset.note ?? '';
        this.dialogTarget.dispatchEvent(new CustomEvent('modal:open'));
        // Start on the current state rather than on the close button.
        this.dialogTarget.querySelector('input[name="day-state"]:checked')?.focus();
    }

    saveDialog(event) {
        event.preventDefault();
        const day = this.selected;
        const checked = this.dialogTarget.querySelector('input[name="day-state"]:checked');
        this.apply([{ day, state: checked?.value ?? day.dataset.state, note: this.dialogNoteTarget.value.trim() }]);
        this.dialogTarget.close();
        day.querySelector('.calendar-day__button').focus();
    }

    // ------------------------------------------------------------------ quick actions
    fillWorkdays() {
        const changes = this.activeDays
            .filter((day) => day.dataset.kind === 'workday' && day.dataset.state === 'empty')
            .map((day) => ({ day, state: 'full' }));
        this.apply(changes);
    }

    clearMonth() {
        const changes = this.activeDays
            .filter((day) => day.dataset.state !== 'empty' || day.dataset.note)
            .map((day) => ({ day, state: 'empty', note: '' }));
        this.apply(changes);
    }

    // ------------------------------------------------------------------ state changes
    apply(changes) {
        if (this.readonlyValue) return;
        const effective = changes.filter(({ day }) => day && day.dataset.kind !== 'outside' && !('disabled' in day.dataset));
        if (effective.length === 0) return;

        const previous = effective.map(({ day }) => ({ day, state: day.dataset.state, note: day.dataset.note ?? '' }));
        effective.forEach(({ day, state, note }) => this.render(day, state, note ?? day.dataset.note ?? ''));
        this.refreshSummary();
        this.refreshSelection();
        this.announce(effective);
        this.persist(effective, previous);
    }

    render(day, state, note) {
        if (!STATES.includes(state)) return;
        day.dataset.state = state;
        day.dataset.note = note;
        if (note) day.dataset.hasNote = ''; else delete day.dataset.hasNote;

        const button = day.querySelector('.calendar-day__button');
        const parts = [day.dataset.label, this.stateLabel(state)];
        if (note) parts.push(`${this.labelsValue.note ?? 'Note'} : ${note}`);
        button.setAttribute('aria-label', parts.join(', '));

        // Visible label: cloned from the <template> rendered by Twig for this state/kind.
        const slot = day.querySelector('.calendar-day__label');
        const templateKey = state === 'empty' ? `kind-${day.dataset.kind}` : state;
        const template = this.labelTemplateTargets.find((t) => t.dataset.key === templateKey);
        slot.replaceChildren(...(template ? Array.from(template.content.cloneNode(true).childNodes) : []));
        if (templateKey === 'kind-holiday') {
            const name = slot.querySelector('[data-slot="holiday"]');
            if (name) name.textContent = day.dataset.holidayName ?? '';
        }
    }

    async persist(changes, previous) {
        if (!this.urlValue) return;
        changes.forEach(({ day }) => { day.dataset.pending = ''; });

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-Token': this.csrfTokenValue,
                },
                body: JSON.stringify({
                    days: changes.map(({ day }) => ({ date: day.dataset.date, state: day.dataset.state, note: day.dataset.note ?? '' })),
                }),
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
        } catch (error) {
            previous.forEach(({ day, state, note }) => this.render(day, state, note));
            this.refreshSummary();
            this.refreshSelection();
            window.dispatchEvent(new CustomEvent('toast:show', {
                detail: { tone: 'error', title: this.labelsValue.saveError, message: this.labelsValue.saveErrorHint },
            }));
        } finally {
            changes.forEach(({ day }) => { delete day.dataset.pending; });
        }
    }

    // ------------------------------------------------------------------ summary + announcements
    counts() {
        const count = (state) => this.activeDays.filter((d) => d.dataset.state === state).length;
        const full = count('full');
        const half = count('half');
        return { full, half, off: count('off'), total: full + half / 2 };
    }

    refreshSummary() {
        const counts = this.counts();
        this.summaryValueTargets.forEach((node) => {
            const key = node.dataset.key;
            if (key === 'unit') {
                node.textContent = this.unitLabel(counts.total);
            } else if (key in counts) {
                node.textContent = this.numberFormat.format(counts[key]);
            }
        });
    }

    announce(changes) {
        if (!this.hasStatusTarget) return;
        const total = this.counts().total;
        const totalText = `${this.numberFormat.format(total)} ${this.unitLabel(total)}`;
        const what = changes.length === 1
            ? `${changes[0].day.dataset.label} : ${this.stateLabel(changes[0].day.dataset.state)}.`
            : `${this.labelsValue.daysUpdated ?? ''} (${changes.length}).`;
        this.statusTarget.textContent = `${what} ${this.labelsValue.total ?? 'Total'} : ${totalText}.`;
    }

    stateLabel(state) {
        return this.labelsValue.states?.[state] ?? state;
    }

    unitLabel(total) {
        const units = this.labelsValue.unit ?? {};
        return units[this.pluralRules.select(total)] ?? units.other ?? '';
    }
}
