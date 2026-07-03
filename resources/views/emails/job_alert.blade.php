@component('mail::message')
# Hello {{ $user->name }},

We found new job openings matching your alerts preference on **Job Hub**:

@foreach($jobs as $job)
### [{{ $job->title }}]({{ $job->url }})
**Company:** {{ $job->company_name }}  
**Location:** {{ $job->location }} ({{ $job->location_type }})  
**Salary:** {{ $job->currency_symbol }}{{ number_format($job->salary_min) }} - {{ $job->currency_symbol }}{{ number_format($job->salary_max) }}

---
@endforeach 

@component('mail::button', ['url' => url('/jobs')])
View All Jobs
@endcomponent 

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent 