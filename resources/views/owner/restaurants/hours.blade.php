@extends('owner.layouts.app')
@section('title', 'Opening Hours')
@section('page-title', 'Opening Hours — ' . $restaurant->name)

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-clock me-2 text-danger"></i>Weekly Opening Hours
    </div>
    <div class="card-body">
        <p class="text-muted" style="font-size:13px">
            Set the hours customers can order from <strong>{{ $restaurant->name }}</strong> each day.
            Leave a day unchecked with times filled in to open normally; check "Closed all day" to block ordering that day entirely.
            This works alongside the main Open/Closed switch — if that switch is off, the restaurant stays closed regardless of schedule.
        </p>

        <form action="{{ route('owner.restaurants.hours.update', $restaurant) }}" method="POST">
            @csrf
            @method('PUT')

            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Day</th>
                        <th>Closed all day</th>
                        <th>Opens at</th>
                        <th>Closes at</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day => $label)
                    @php $h = $hours->get($day); @endphp
                    <tr>
                        <td class="fw-600">{{ $label }}</td>
                        <td>
                            <input type="checkbox" class="form-check-input day-closed-toggle"
                                data-day="{{ $day }}"
                                name="days[{{ $day }}][is_closed]" value="1"
                                {{ $h && $h->is_closed ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="time" class="form-control form-control-sm" style="max-width:150px"
                                id="opens-{{ $day }}"
                                name="days[{{ $day }}][opens_at]"
                                value="{{ $h?->opens_at ? substr($h->opens_at, 0, 5) : '09:00' }}"
                                {{ $h && $h->is_closed ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input type="time" class="form-control form-control-sm" style="max-width:150px"
                                id="closes-{{ $day }}"
                                name="days[{{ $day }}][closes_at]"
                                value="{{ $h?->closes_at ? substr($h->closes_at, 0, 5) : '22:00' }}"
                                {{ $h && $h->is_closed ? 'disabled' : '' }}>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="btn btn-danger">
                <i class="bi bi-check-lg me-1"></i>Save Hours
            </button>
            <a href="{{ route('owner.restaurants.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.day-closed-toggle').forEach(cb => {
    cb.addEventListener('change', function() {
        const day = this.dataset.day;
        document.getElementById('opens-' + day).disabled = this.checked;
        document.getElementById('closes-' + day).disabled = this.checked;
    });
});
</script>
@endpush
