<div class="lg:border-t lg:border-b lg:border-gray-200">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Progress">
        <ol role="list"
            class="overflow-hidden rounded-md lg:flex lg:rounded-none lg:border-r lg:border-l lg:border-gray-200">
            {{--            <li class="relative overflow-hidden lg:flex-1">--}}
            {{--                <div class="overflow-hidden rounded-t-md border border-b-0 border-gray-200 lg:border-0">--}}
            {{--                    <!-- Completed Step -->--}}
            {{--                    <a href="#" class="group">--}}
            {{--                        <span--}}
            {{--                            class="absolute top-0 left-0 h-full w-1 bg-transparent group-hover:bg-gray-200 lg:top-auto lg:bottom-0 lg:h-1 lg:w-full"--}}
            {{--                            aria-hidden="true"></span>--}}
            {{--                        <span class="flex items-start px-6 py-5 text-sm font-medium">--}}
            {{--              <span class="shrink-0">--}}
            {{--                <span class="flex size-10 items-center justify-center rounded-full bg-indigo-600">--}}
            {{--                  <svg class="size-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"--}}
            {{--                       data-slot="icon">--}}
            {{--                    <path fill-rule="evenodd"--}}
            {{--                          d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z"--}}
            {{--                          clip-rule="evenodd"/>--}}
            {{--                  </svg>--}}
            {{--                </span>--}}
            {{--              </span>--}}
            {{--              <span class="mt-0.5 ml-4 flex min-w-0 flex-col">--}}
            {{--                <span class="text-sm font-medium">Job Details</span>--}}
            {{--                <span class="text-sm font-medium text-gray-500">Vitae sed mi luctus laoreet.</span>--}}
            {{--              </span>--}}
            {{--            </span>--}}
            {{--                    </a>--}}
            {{--                </div>--}}
            {{--            </li>--}}
            @foreach ($stages as $key => $stage)
                <li class="relative overflow-hidden lg:flex-1">
                    <div class="overflow-hidden border border-gray-200 lg:border-0">
                        <!-- Current Step -->
                        <a href="#" aria-current="step">
                            <span
                                class="absolute top-0 left-0 h-full w-1 bg-indigo-600 lg:top-auto lg:bottom-0 lg:h-1 lg:w-full"
                                aria-hidden="true"></span>
                            <span class="flex items-start px-6 py-5 text-sm font-medium lg:pl-9">
                              <span class="shrink-0">
                                  @if ($key < $currentStage)
                                      <span class="flex size-10 items-center justify-center rounded-full bg-indigo-600">
                                      <svg class="size-6 text-white" viewBox="0 0 24 24" fill="currentColor"
                                           aria-hidden="true"
                                           data-slot="icon">
                                        <path fill-rule="evenodd"
                                              d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z"
                                              clip-rule="evenodd"/>
                                      </svg>
                                    </span>
                                  @else
                                      <span
                                          class="flex size-10 items-center justify-center rounded-full border-2 border-indigo-600">
                                  <span class="text-indigo-600">{{ ltrim('0'.$key, 2) }}</span>
                                </span>
                                  @endif
                              </span>
                              <span class="mt-0.5 ml-4 flex min-w-0 flex-col">
                                <span class="text-sm font-medium text-indigo-600">{{ $stage  }}</span>
                              </span>
                            </span>
                        </a>
                        <!-- Separator -->
                        <div class="absolute inset-0 top-0 left-0 hidden w-3 lg:block" aria-hidden="true">
                            <svg class="size-full text-gray-300" viewBox="0 0 12 82" fill="none"
                                 preserveAspectRatio="none">
                                <path d="M0.5 0V31L10.5 41L0.5 51V82" stroke="currentcolor"
                                      vector-effect="non-scaling-stroke"/>
                            </svg>
                        </div>
                    </div>
                </li>
            @endforeach
            <li class="relative overflow-hidden lg:flex-1">
                <div class="overflow-hidden rounded-b-md border border-t-0 border-gray-200 lg:border-0">
                    <!-- Upcoming Step -->
                    <a href="#" class="group">
                        <span
                            class="absolute top-0 left-0 h-full w-1 bg-transparent group-hover:bg-gray-200 lg:top-auto lg:bottom-0 lg:h-1 lg:w-full"
                            aria-hidden="true"></span>
                        <span class="flex items-start px-6 py-5 text-sm font-medium lg:pl-9">
              <span class="shrink-0">
                <span class="flex size-10 items-center justify-center rounded-full border-2 border-gray-300">
                  <span class="text-gray-500">03</span>
                </span>
              </span>
              <span class="mt-0.5 ml-4 flex min-w-0 flex-col">
                <span class="text-sm font-medium text-gray-500">Preview</span>
                <span class="text-sm font-medium text-gray-500">Penatibus eu quis ante.</span>
              </span>
            </span>
                    </a>
                    <!-- Separator -->
                    <div class="absolute inset-0 top-0 left-0 hidden w-3 lg:block" aria-hidden="true">
                        <svg class="size-full text-gray-300" viewBox="0 0 12 82" fill="none" preserveAspectRatio="none">
                            <path d="M0.5 0V31L10.5 41L0.5 51V82" stroke="currentcolor"
                                  vector-effect="non-scaling-stroke"/>
                        </svg>
                    </div>
                </div>
            </li>
        </ol>
    </nav>
</div>

