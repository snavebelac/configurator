<nav aria-label="Progress" class="mt-6 px-3">
    <ol role="list" class="bg-white divide-y divide-gray-300 rounded-md border border-gray-300 md:flex md:divide-y-0">
        @foreach($stages as $key => $stage)
            <li class="relative md:flex md:flex-1">
                @if ($currentStage == $key)
                    <!-- Current Step -->
                    <div class="flex items-center px-6 py-4 text-sm font-medium" aria-current="step">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full border-2 border-indigo-600">
                            <span class="text-indigo-600">{{ str_pad($key, 2, '0', 0) }}</span>
                        </span>
                        <span class="ml-4 text-sm font-medium text-indigo-600"> {{ $stage }}</span>
                    </div>
                @elseif ($currentStage > $key)
                    <!-- Completed Step -->
                    <div class="group flex w-full items-center">
                        <span class="flex items-center px-6 py-4 text-sm font-medium">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 group-hover:bg-indigo-800">
                                <svg class="size-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                                    <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="ml-4 text-sm font-medium text-gray-900">{{ $stage }}</span>
                        </span>
                    </div>
                @else
                    <!-- Upcoming Step -->
                    <div class="group flex items-center">
                        <span class="flex items-center px-6 py-4 text-sm font-medium">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full border-2 border-gray-300 group-hover:border-gray-400">
                                <span class="text-gray-500 group-hover:text-gray-900">{{ str_pad($key, 2, '0', 0) }}</span>
                            </span>
                            <span class="ml-4 text-sm font-medium text-gray-500 group-hover:text-gray-900">{{ $stage }}</span>
                        </span>
                    </div>
                @endif
                @if (!$loop->last)
                    <!-- Arrow separator for lg screens and up -->
                    <div class="absolute top-0 right-0 hidden h-full w-5 md:block" aria-hidden="true">
                        <svg class="size-full text-gray-300" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
                            <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke" stroke="currentcolor" stroke-linejoin="round" />
                        </svg>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

