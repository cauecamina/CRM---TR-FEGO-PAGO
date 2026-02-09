<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise de Perfil | Tráfego Pago</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9', // Sky 500
                        secondary: '#1e3a8a', // Blue 900
                        dark: '#0b1726', // Custom Dark Blue
                        accent: '#1d4ed8', // Blue 700
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
        .fade-enter-start, .fade-leave-end { opacity: 0; transform: translateX(20px); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-dark to-secondary min-h-screen flex items-center justify-center p-4 text-white overflow-hidden">

    <div x-data="quizApp()" class="w-full max-w-lg bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl shadow-2xl overflow-hidden relative" style="min-height: 600px">
        
        <!-- Barra de Progresso -->
        <div class="h-1 bg-gray-700 w-full absolute top-0 left-0 z-10">
            <div class="h-full bg-primary transition-all duration-500 ease-out" :style="'width: ' + progress + '%'"></div>
        </div>

        <!-- Conteúdo do Form -->
        <div class="absolute inset-0 w-full h-full">
            
            <!-- Questions Loop -->
            <template x-for="(question, index) in questions" :key="index">
                <div x-show="currentStep === index" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-12"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 -translate-x-12"
                     class="absolute inset-0 overflow-y-auto custom-scrollbar">
                     
                    <div class="min-h-full p-8 pb-32 flex flex-col justify-center">
                        <h2 class="text-2xl md:text-3xl font-bold mb-6 text-white" x-text="question.title"></h2>
                    
                    <!-- Input Type: Select -->
                    <template x-if="question.type === 'select'">
                        <div class="space-y-3">
                            <template x-for="option in question.options" :key="option">
                                <button @click="selectOption(index, option)" 
                                        class="w-full text-left p-4 rounded-xl border border-white/10 hover:bg-primary/20 hover:border-primary transition-all duration-200 group flex items-center justify-between"
                                        :class="formData[question.model] === option ? 'bg-primary/30 border-primary' : 'bg-white/5'">
                                    <span x-text="option" class="font-medium text-gray-200 group-hover:text-white"></span>
                                    <span x-show="formData[question.model] === option" class="text-primary text-xl">✓</span>
                                </button>
                            </template>
                        </div>
                    </template>

                    <!-- Input Type: Text/Email/Tel -->
                    <template x-if="['text', 'email', 'tel'].includes(question.type)">
                        <div>
                            <input :type="question.type" 
                                   x-model="formData[question.model]" 
                                   @keyup.enter="nextStep()"
                                   class="w-full bg-transparent border-b-2 border-white/20 focus:border-primary text-xl py-3 text-white placeholder-gray-500 outline-none transition-colors"
                                   :placeholder="question.placeholder">
                            <p class="text-sm text-gray-400 mt-2">Pressione Enter para continuar</p>
                        </div>
                    </template>

                    </div>
                </div>
            </template>

            <!-- Loading State -->
            <div x-show="isSubmitting" 
                 x-transition
                 class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-black/80 backdrop-blur-sm">
                <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-lg font-medium animate-pulse">Analisando seu perfil com IA...</p>
            </div>

            <!-- Success State -->
            <div x-show="isSuccess" 
                 class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-green-900/90 backdrop-blur-md text-center p-8">
                <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center text-4xl mb-4 shadow-lg shadow-green-500/50">✓</div>
                <h3 class="text-3xl font-bold mb-2">Tudo certo!</h3>
                <p class="text-gray-200 mb-6">Recebemos seus dados e nossa IA já está analisando o melhor plano para você.</p>
                <p class="text-sm text-gray-400">Entraremos em contato em breve.</p>
            </div>

        </div>

        <!-- Navigation Buttons -->
        <div class="absolute bottom-0 w-full p-6 flex justify-between items-center bg-gradient-to-t from-black/50 to-transparent" x-show="!isSubmitting && !isSuccess">
            <button @click="prevStep()" 
                    x-show="currentStep > 0"
                    class="text-sm text-gray-400 hover:text-white flex items-center gap-1 transition-colors">
                ← Voltar
            </button>
            <div class="flex-1"></div>
            <button @click="nextStep()" 
                    :disabled="!canProceed()"
                    class="bg-primary hover:bg-primary/80 text-white font-bold py-3 px-8 rounded-full shadow-lg shadow-primary/30 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center gap-2">
                <span x-text="currentStep === questions.length - 1 ? 'Finalizar' : 'Próximo'"></span>
                <span x-show="currentStep < questions.length - 1">→</span>
            </button>
        </div>

    </div>

    <script>
        function quizApp() {
            return {
                currentStep: 0,
                isSubmitting: false,
                isSuccess: false,
                questions: [
                    {
                        title: "Qual é o faturamento atual da sua empresa?",
                        type: 'select',
                        model: 'faturamento',
                        options: ['Até R$ 10.000', 'R$ 10.000 a R$ 50.000', 'R$ 50.000 a R$ 200.000', 'Acima de R$ 200.000']
                    },
                    {
                        title: "Quanto pretende investir em tráfego pago?",
                        type: 'select',
                        model: 'investimento',
                        options: ['Até R$ 1.000', 'R$ 1.000 a R$ 3.000', 'R$ 3.000 a R$ 5.000', 'R$ 5.000 a R$ 10.000', 'Acima de R$ 10.000']
                    },
                    {
                        title: "Qual o Instagram da sua empresa?",
                        type: 'text',
                        model: 'instagram',
                        placeholder: '@seu_perfil'
                    },
                    {
                        title: "Qual é o ramo da sua empresa?",
                        type: 'select',
                        model: 'ramo',
                        options: ['E-commerce', 'Serviços Locais', 'Infoproduto', 'Software / SaaS', 'Outro']
                    },
                    {
                        title: "Você já faz tráfego pago atualmente?",
                        type: 'select',
                        model: 'faz_trafego',
                        options: ['Sim', 'Não', 'Já fiz, mas parei']
                    },
                    {
                        title: "Qual seu objetivo principal?",
                        type: 'select',
                        model: 'objetivo',
                        options: ['Aumentar Vendas', 'Gerar Leads', 'Branding / Reconhecimento', 'Conseguir Seguidores']
                    },
                    {
                        title: "Como devemos te chamar?",
                        type: 'text',
                        model: 'nome',
                        placeholder: 'Seu nome completo'
                    },
                    {
                        title: "Qual seu melhor email?",
                        type: 'email',
                        model: 'email',
                        placeholder: 'nome@exemplo.com'
                    },
                    {
                        title: "Qual seu WhatsApp?",
                        type: 'tel',
                        model: 'telefone',
                        placeholder: '(XX) 9XXXX-XXXX'
                    }
                ],
                formData: {
                    faturamento: '',
                    investimento: '',
                    instagram: '',
                    ramo: '',
                    faz_trafego: '',
                    objetivo: '',
                    nome: '',
                    email: '',
                    telefone: ''
                },
                
                get progress() {
                    return ((this.currentStep + 1) / this.questions.length) * 100;
                },

                canProceed() {
                    const currentModel = this.questions[this.currentStep].model;
                    const value = this.formData[currentModel];
                    return value && value.trim() !== '';
                },

                selectOption(index, option) {
                    const model = this.questions[index].model;
                    this.formData[model] = option;
                    setTimeout(() => {
                        this.nextStep();
                    }, 300); // Small delay for visual feedback
                },

                nextStep() {
                    if (!this.canProceed()) return;

                    if (this.currentStep < this.questions.length - 1) {
                        this.currentStep++;
                    } else {
                        this.submitForm();
                    }
                },

                prevStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                    }
                },

                async submitForm() {
                    this.isSubmitting = true;
                    
                    try {
                        const response = await fetch('api/new-lead.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.isSuccess = true;
                        } else {
                            alert('Houve um erro: ' + (result.message || 'Tente novamente.'));
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Erro de conexão. Tente novamente.');
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
