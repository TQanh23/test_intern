@props(['dark' => false])

{{-- Logo chữ, font tiêu đề như header trong Figma. Đặt trên nền tối thì truyền :dark="true". --}}
<span {{ $attributes->merge(['class' => 'font-display font-bold tracking-tight '.($dark ? 'text-white' : 'text-ink')]) }}>ExamPro</span>
