@extends('layouts.pic')

@section('title', 'Langkah '.$step.' — '.$project->mosque_name)

@section('content')
    @livewire('wizard.wizard-step', ['token' => $token, 'step' => $step], key('wizard-'.$step))
@endsection
