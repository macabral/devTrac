<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
 
	<div class="grid grid-cols-2">
		<div>
			<x-splade-form name="form" method="post" :action="route('dashboard.project')" :default="$input" class="mt-4 sm:px-6 lg:px-8 grid grid-cols-3 md:grid-cols-3 gap-3" preserve-scroll>
			<div>
					<div>
					<x-splade-select id="projects_id" name="projects_id" :options="$proj" option-label="title" option-value="projects_id"  placeholder="Projeto" autofocus />
					</div>
					<div  class="mt-2">
					<x-splade-select id="sprints_id" name="sprints_id" :options="$sprints" option-label="version" option-value="id" placeholder="Sprint" remote-url="`api/sprints-dashboard/${form.projects_id}`" /> 
					</div>
					<div  class="mt-2"> 
					<x-splade-submit :label="__('Select Project')" />
					</div>
			</div>
			</x-splade-form>
		</div>
		<div>
				<div class="w-80 p-6 border-8 rounded-md mt-10 bg-white px-2 inline-block align-middle text-center text-blue-800">
					Total de Tíquetes<br>
					{{ $total }}
				</div>

		</div>
	</div>

    <div class="flex flex-wrap mt-6 sm:px-6 lg:px-8 mx-6 mr-6 grid grid-cols-2 md:grid-cols-2 gap-6 bg-white">
    @if (count($stats) > 0)
      <div>
        <div>
        <table class="w-full bg-white mt-6 text-left text-sm font-light border border-slate-400 rounded">
            <thead class="border-b font-medium dark:border-neutral-500">
                <tr>
                  <th class="text-left">Projeto</th>
                  <th class="text-left">Sprint</th>
                  <th class="text-left">Início</th>
                  <th class="text-left">Fim</th>
                  <th class="text-left">Tipo</th>
                  <th class="text-center">Story Points</th>
				  <th class="text-center">PF</th>
				  <th class="text-center">Total</th>
                  <th class="text-center">Open</th>
                  <th class="text-center">Testing</th>
                  <th class="text-center">Closed</th>

                </tr>
              </thead>
              <tbody>
                    @php
                        $totalOpen = 0; $totalClosed = 0; $totalTesting = 0; $totalStory = 0; $totalpf = 0;
                    @endphp
                    @foreach($stats as $item)
                    @php
                        $totalOpen +=  $item['open'];
                        $totalClosed += $item['closed'];
                        $totalTesting += $item['testing'];
                        $totalStory += $item['storypoint'];
						$totalpf += $item['pf'];
                    @endphp
                    <tr class="border-b dark:border-neutral-500">
                        <td>{{ $item['project'] }}</td>
                        <td>{{ $item['sprint'] }}</td>
                        <td>{{ date('d/m/Y', strtotime($item['start'])) }}</td>
                        <td>{{ date('d/m/Y', strtotime($item['end'])) }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td class="text-center">{{ $item['storypoint'] }}</td>
						<td class="text-center">{{ $item['pf'] }}</td>
						<td class="text-center">{{ $item['open'] + $item['closed'] + $item['testing'] }}</td>
                        <td class="text-center">{{ $item['open'] }}</td>
                        <td class="text-center">{{ $item['testing'] }}</td>
                        <td class="text-center">{{ $item['closed'] }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-b dark:border-neutral-500">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-center">{{ $totalStory }}</td>
						<td class="text-center">{{ $totalpf }}</td>
						<td class="text-center">{{ $totalOpen + $totalClosed + $totalTesting }}</td>
                        <td class="text-center">{{ $totalOpen }}</td>
                        <td class="text-center">{{ $totalTesting }}</td>
                        <td class="text-center">{{ $totalClosed }}</td>
                        
                    </tr>
              </tbody>
        </table>
        </div>
        <div>
          <br>
          <div id="chart1" name="chart1"></div>
        </div>

		@if (count($perdev) > 0)
		<div>
		   <table class="min-w-full bg-white mt-6 text-left text-sm font-light rounded">
			   <thead class="border-b font-medium dark:border-neutral-500">
				   <tr>
					 <th class="text-left">Dev</th>
					 <th class="text-left">Projeto</th>
					 <th class="text-left">Sprint</th>
					 <th class="text-left">Tipo</th>
					 <th class="text-center">Total</th>
					 <th class="text-center">Open</th>
					 <th class="text-center">Testing</th>
					 <th class="text-center">Closed</th>
				   </tr>
				 </thead>
				 <tbody>
					   @foreach($perdev as $item)
					   <tr class="border-b dark:border-neutral-500">
						 <td>{{ $item['name'] }}</td>
						 <td>{{ $item['project'] }}</td>
						 <td>{{ $item['sprint'] }}</td>
						 <td>{{ $item['type'] }}</td>
						 <td class="text-center">{{ $item['open'] + $item['closed'] + $item['testing'] }}</td>
						 <td class="text-center">{{ $item['open'] }}</td>
						 <td class="text-center">{{ $item['testing'] }}</td>
						 <td class="text-center">{{ $item['closed'] }}</td>
					   </tr>
					   @endforeach
				 </tbody>
		   </table>
	   </div>
	   @endif

      </div>
    @else
    <div class="rounded">
		<br>
        <p>Você não possui tarefas no momento.</p>
		<br>
    </div>
  @endif

  
  <div class="rounded bg-white mt-6">

    <div id="chart2" name="chart2"></div>

	<div id="chart3" name="chart3"></div>

	@if ($pf_medio != 0)
		<div id="chart4" name="chart4"></div>
	@endif
	
	<br><br>

</div>

</div>
<br><br>
    <x-splade-script>

        <!-- Sprint Burndown -->

        @if (! is_null($chart1))

			var cat = "{{ $chart1['categories'] }}"
			var categories = cat.split(',')
			var data1 = {{ $chart1['data1'] }}
			var data2 = {{ $chart1['data2'] }}
			var title = "{{ $chart1['title'] }}"

			var options = {
				series: [
				{
					name: "Estimado",
					data: data1
				},
				{
					name: "Real",
					data:  data2
				}
				],
				chart: {
				height: '200%',
				width:'100%',
				type: 'line',
				zoom: {
				enabled: false
				}
			},
			dataLabels: {
				enabled: false
			},
			stroke: {
				curve: 'straight'
			},
			title: {
				text: title,
				align: 'left'
			},
			grid: {
				row: {
				colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
				opacity: 0.5
				},
			},
			xaxis: {
				categories: categories,
			}
			};
	
			var chart1 = new ApexCharts(document.querySelector("#chart1"), options);
			chart1.render();

		@endif

		<!-- Sprint -->

		@if (! is_null($chart2))

			var cat2 = "{{ $chart2['categories']  }}"
			var categ2 = cat2.split(',')
			var data1 = {{ $chart2['data1'] }}
			var data2 = {{ $chart2['data2'] }}
			var data3 = {{ $chart2['data3'] }}
			var title = "{{ $chart2['title'] }}"

			var ar = []
			categ2.forEach((elem) => {
				ar.push(elem)

			})
					
			var options = {
				series: [
				{
					name: 'Melhoria',
					data: data1
				}, {
					name: 'Defeito',
					data: data2
				}, {
					name: 'Suporte Técnico',
					data: data3
				}],
				chart: {
					type: 'bar',
					height: 350,
					stacked: true,
					toolbar: {
					show: true
				},
				zoom: {
					enabled: true
				}
			},
			responsive: [{
				breakpoint: 480,
				options: {
				legend: {
					position: 'bottom',
					offsetX: -10,
					offsetY: 0
				}
				}
			}],
			plotOptions: {
				bar: {
				horizontal: false,
				borderRadius: 10,
				dataLabels: {
					total: {
					enabled: true,
					style: {
						fontSize: '13px',
						fontWeight: 900
					}
					}
				}
				},
			},
			xaxis: {
				type: 'text',
				categories: ar,
			},
			legend: {
				position: 'right',
				offsetY: 40
			},
			fill: {
				opacity: 1
			},
			title: {
				text: title,
				align: 'left'
			},
			};
	
			var chart2 = new ApexCharts(document.querySelector("#chart2"), options);
			chart2.render();
		
		@endif

        <!-- Sprint/Story Points -->
		@if (! is_null($chart3))
			var cat2 = "{{ $chart3['categories']  }}"
			var categ2 = cat2.split(',')
			var data1 = {{ $chart3['data1'] }}
			var title = "{{ $chart3['title'] }}"

			var ar = []
			categ2.forEach((elem) => {
				ar.push(elem)

			})

			var options = {
				series: [{
				name: title,
				data: data1
			}],
				chart: {
				type: 'bar',
				height: 350,
				stacked: true,
				toolbar: {
				show: true
				},
				zoom: {
				enabled: true
				}
			},
			responsive: [{
				breakpoint: 480,
				options: {
				legend: {
					position: 'bottom',
					offsetX: -10,
					offsetY: 0
				}
				}
			}],
			plotOptions: {
				bar: {
				horizontal: false,
				borderRadius: 10,
				dataLabels: {
					total: {
					enabled: false,
					style: {
						fontSize: '13px',
						fontWeight: 900
					}
					}
				}
				},
			},
			xaxis: {
				type: 'text',
				categories: ar,
			},
			fill: {
				opacity: 1
			},
			title: {
				text: title + ' (Média de  {{ $storypoint_medio }})',
				align: 'left'
			},
			};
	
			var chart3 = new ApexCharts(document.querySelector("#chart3"), options);
			chart3.render();

        @endif

		<!-- Sprint/PF-->
		@if (! count($chart4) == 0)
			var cat2 = "{{ $chart4['categories']  }}"
			var categ2 = cat2.split(',')
			var data1 = {{ $chart4['data1'] }}
			var title = "{{ $chart4['title'] }}"

			var ar = []
			categ2.forEach((elem) => {
				ar.push(elem)
			})

			var options = {
				series: [{
				name: title,
				data: data1
			}],
				chart: {
					type: 'bar',
					height: 350,
					stacked: true,
					toolbar: {
						show: true
					},
					zoom: {
						enabled: true
					}
			},
			responsive: [{
				breakpoint: 480,
				options: {
				legend: {
					position: 'bottom',
					offsetX: -10,
					offsetY: 0
				}
				}
			}],
			plotOptions: {
				bar: {
				horizontal: false,
				borderRadius: 10,
				dataLabels: {
					total: {
						enabled: false,
						style: {
							fontSize: '13px',
							fontWeight: 900
						}
					}
				}
				},
			},
			xaxis: {
				type: 'text',
				categories: ar,
			},
			fill: {
				opacity: 1
			},
			title: {
				text: title + ' (Média de  {{ $pf_medio }})',
				align: 'left'
			},
			};

			var chart4 = new ApexCharts(document.querySelector("#chart4"), options);
			chart4.render();
		@endif

    </x-splade-script>
</x-app-layout>

