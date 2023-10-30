<div>
    <div class="wrapper">
        <div class="@if($step == 2) hidden @endif">
            <div class="container">
                <h1 class="font-bold mb-4">Please Insert your Information</h1>
                <div class="flex flex-col mb-4">
                    <label for="name" class="mb-2">Your Name</label>
                    <input class="border border-gray-300 p-3 rounded" type="text" id="name" wire:model="customer_name">
                </div>
                <div class="flex flex-col mb-4">
                    <label for="phone" class="mb-2">Phone Number</label>
                    <input class="border border-gray-300 p-3 rounded" type="text" id="phone" wire:model="customer_phone">
                </div>

                <div class="flex flex-col mb-4">
                    <button class="border-0 p-3 bg-amber-500 text-white rounded" wire:click="storeCustomer">Submit</button>
                </div>

            </div>
        </div>
        <div class="@if($step == 1) hidden @endif">
            <div class="container">
                <canvas id="wheel"></canvas>
                <button id="spin-btn">Spin</button>
                <img src="{{ asset('img/spinner-arrow-.svg') }}" alt="spinner-arrow" />
            </div>
            <div id="final-value" wire:ignore>
                <p>Click On The Spin Button To Start</p>
            </div>
        </div>
    </div>
    <!-- Chart JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Chart JS Plugin for displaying text over chart -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.1.0/chartjs-plugin-datalabels.min.js"></script>
    <!-- Script -->
    <script>
        let prizes = @json($prizes);
        const labels = @json($labels);  // Our new dynamic labels
        const wheel = document.getElementById("wheel");
        const spinBtn = document.getElementById("spin-btn");
        const finalValue = document.getElementById("final-value");
        //Object that stores values of minimum and maximum angle for a value
        const rotationValues = @json($rotationValues);
        //Size of each piece
        const data = @json($data);
        //background color for each piece
        var pieColors = [
            "#8b35bc",
            "#b163da",
            "#8b35bc",
            "#b163da",
            "#8b35bc",
            "#b163da",
        ];
        //Create chart
        let myChart = new Chart(wheel, {
            //Plugin for displaying text on pie chart
            plugins: [ChartDataLabels],
            //Chart Type Pie
            type: "pie",
            data: {
                //Labels(values which are to be displayed on chart)
                labels: prizes.map(prize => prize.title),
                //Settings for dataset/pie
                datasets: [
                    {
                        backgroundColor: pieColors,
                        data: data,
                    },
                ],
            },
            options: {
                //Responsive chart
                responsive: true,
                animation: { duration: 0 },
                plugins: {
                    //hide tooltip and legend
                    tooltip: false,
                    legend: {
                        display: false,
                    },
                    //display labels inside pie chart
                    datalabels: {
                        color: "#ffffff",
                        rotation: (context) =>
                            context.dataIndex * (360 / context.chart.data.labels.length) +
                            360 / context.chart.data.labels.length / 2 +
                            270 +
                            context.chart.options.rotation,
                        formatter: (_, context) => context.chart.data.labels[context.dataIndex],
                        font: { size: 24 },
                    },
                },
            },
        });
        //display value based on the randomAngle
        const valueGenerator = (angleValue) => {
            for (let i of rotationValues) {
                //if the angleValue is between min and max then display it
                if (angleValue >= i.minDegree && angleValue <= i.maxDegree) {
                    console.log("Detected Segment:", i);
                    finalValue.innerHTML = `<p>Congratulations you won ${i.value}</p>`;
                    storeWinner(i.id);
                    spinBtn.disabled = false;
                    break;
                }
            }
        };

        const storeWinner = (value) => {
            @this.storeWinner(value);
        };

        var prizesData = @json($prizes);

        function getRandomDegreeFromChances(prizes) {
            let totalChance = prizes.reduce((acc, prize) => acc + prize.remainingChance, 0);
            let randomValue = Math.random() * totalChance;
            let accumulatedChance = 0;

            for (let prize of prizes) {
                accumulatedChance += prize.remainingChance;
                if (randomValue <= accumulatedChance) {
                    // Here, you would return a degree based on the prize.id
                    // For example:
                    let index = prizes.indexOf(prize);
                    let degreePerPrize = 360 / prizes.length;
                    let minDegree = index * degreePerPrize;
                    let maxDegree = minDegree + degreePerPrize;
                    // Return a random degree between minDegree and maxDegree for the prize
                    return Math.floor(Math.random() * (maxDegree - minDegree + 1) + minDegree);
                }
            }
        }

        //Spinner count
        let count = 10;
        //100 rotations for animation and last rotation for result
        let resultValue = 101;

        //Start spinning
        spinBtn.addEventListener("click", () => {
            spinBtn.disabled = true;
            finalValue.innerHTML = `<p>Good Luck!</p>`;

            let randomDegree = getRandomDegreeFromChances(prizesData);
            let currentRotation = 0;
            let speed = 10;

            // Determine where the randomDegree will be when it's at the top (0-degree position)
            let offsetToZero = 360 - randomDegree;

            // Adjust totalRotation to align the segment at the top
            const totalRotation = 5 * 360 + offsetToZero;

            let rotationInterval = window.setInterval(() => {
                currentRotation += speed;

                // Slow down logic: the closer we are to the stopping angle, the slower we go.
                const remainingRotation = totalRotation - currentRotation;
                if (remainingRotation < 360 && speed > 1) {
                    speed -= 0.5;
                }

                if (remainingRotation <= speed) {
                    speed = remainingRotation; // Ensure we stop exactly at 0 degrees after the desired segment.
                }

                if (currentRotation >= totalRotation) {
                    console.log('Expected to align segment to 0-degree position');
                    console.log('Actual stop degree:', myChart.options.rotation);
                    valueGenerator(randomDegree);  // still use randomDegree for value generation
                    clearInterval(rotationInterval);
                }

                // Set rotation for pie chart and update the chart
                myChart.options.rotation = currentRotation % 360;
                myChart.update();

            }, 10);
        });


    </script>
</div>
