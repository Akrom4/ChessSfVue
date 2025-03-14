<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-[var(--color-nav-start)] mb-8">
            Options de boutons d'action pour les cartes
        </h1>

        <!-- Example 1: Top-right corner buttons -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">1. Boutons en haut à droite</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1010/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                        <!-- Action buttons -->
                        <div class="absolute top-2 right-2 flex gap-2">
                            <button
                                class="w-8 h-8 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-blue-600">
                                <PlusIcon class="h-5 w-5" />
                            </button>
                            <button
                                class="w-8 h-8 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-gray-700">
                                <InformationCircleIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">La défense française (Non suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">12 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les blancs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Eugene Znosko-Borovsky</span>
                    </template>
                    <p class="line-clamp-2 mb-2">La manière la plus intransigeante d'attaquer la défense française.</p>
                </Card>

                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1025/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                        <!-- Action buttons -->
                        <div class="absolute top-2 right-2 flex gap-2">
                            <button
                                class="w-8 h-8 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-green-600">
                                <CheckIcon class="h-5 w-5" />
                            </button>
                            <a href="#"
                                class="w-8 h-8 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-gray-700">
                                <ArrowTopRightOnSquareIcon class="h-5 w-5" />
                            </a>
                        </div>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">Défense Sicilienne (Déjà suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">8 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les noirs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Garry Kasparov</span>
                    </template>
                    <p class="line-clamp-2 mb-2">Une ouverture d'échecs hyperagressive pour les noirs.</p>
                </Card>
            </div>
        </div>

        <!-- Example 2: Bottom action bar -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">2. Barre d'actions en bas</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1010/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">La défense française (Non suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">12 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les blancs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Eugene Znosko-Borovsky</span>
                    </template>
                    <p class="line-clamp-2 mb-2">La manière la plus intransigeante d'attaquer la défense française.</p>
                    <!-- Action buttons -->
                    <div class="mt-4 flex border-t border-gray-200 pt-4">
                        <button
                            class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-blue-600 hover:bg-blue-50 rounded-md">
                            <PlusIcon class="h-4 w-4" />
                            <span>Suivre</span>
                        </button>
                        <button
                            class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-gray-700 hover:bg-gray-50 rounded-md">
                            <InformationCircleIcon class="h-4 w-4" />
                            <span>Détails</span>
                        </button>
                    </div>
                </Card>

                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1025/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">Défense Sicilienne (Déjà suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">8 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les noirs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Garry Kasparov</span>
                    </template>
                    <p class="line-clamp-2 mb-2">Une ouverture d'échecs hyperagressive pour les noirs.</p>
                    <!-- Action buttons -->
                    <div class="mt-4 flex border-t border-gray-200 pt-4">
                        <button
                            class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-green-600 hover:bg-green-50 rounded-md">
                            <CheckIcon class="h-4 w-4" />
                            <span>Suivi</span>
                        </button>
                        <a href="#"
                            class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-gray-700 hover:bg-gray-50 rounded-md">
                            <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                            <span>Accéder</span>
                        </a>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Example 3: Floating action buttons -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">3. Boutons flottants sur l'image</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <div class="relative w-full h-full">
                            <img src="https://picsum.photos/id/1010/600/600" alt="Pièce d'échecs"
                                class="w-full h-full object-cover" />
                            <!-- Floating action buttons -->
                            <div
                                class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300 bg-black bg-opacity-30">
                                <button
                                    class="mx-2 w-10 h-10 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-blue-600">
                                    <PlusIcon class="h-6 w-6" />
                                </button>
                                <button
                                    class="mx-2 w-10 h-10 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-gray-700">
                                    <InformationCircleIcon class="h-6 w-6" />
                                </button>
                            </div>
                        </div>
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">La défense française (Non suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">12 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les blancs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Eugene Znosko-Borovsky</span>
                    </template>
                    <p class="line-clamp-2 mb-2">La manière la plus intransigeante d'attaquer la défense française.</p>
                </Card>

                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <div class="relative w-full h-full">
                            <img src="https://picsum.photos/id/1025/600/600" alt="Pièce d'échecs"
                                class="w-full h-full object-cover" />
                            <!-- Floating action buttons -->
                            <div
                                class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300 bg-black bg-opacity-30">
                                <button
                                    class="mx-2 w-10 h-10 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-green-600">
                                    <CheckIcon class="h-6 w-6" />
                                </button>
                                <a href="#"
                                    class="mx-2 w-10 h-10 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center text-gray-700">
                                    <ArrowTopRightOnSquareIcon class="h-6 w-6" />
                                </a>
                            </div>
                        </div>
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">Défense Sicilienne (Déjà suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">8 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les noirs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Garry Kasparov</span>
                    </template>
                    <p class="line-clamp-2 mb-2">Une ouverture d'échecs hyperagressive pour les noirs.</p>
                </Card>
            </div>
        </div>

        <!-- Example 4: Inline with metadata -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">4. Actions intégrées avec les métadonnées</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1010/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <div class="flex justify-between items-start">
                            <h3 class="text-base font-semibold">La défense française (Non suivi)</h3>
                            <div class="flex gap-1 -mt-1">
                                <button class="text-blue-600 hover:text-blue-800">
                                    <PlusIcon class="h-5 w-5" />
                                </button>
                                <button class="text-gray-600 hover:text-gray-800">
                                    <InformationCircleIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">12 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les blancs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Eugene Znosko-Borovsky</span>
                    </template>
                    <p class="line-clamp-2 mb-2">La manière la plus intransigeante d'attaquer la défense française.</p>
                </Card>

                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1025/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <div class="flex justify-between items-start">
                            <h3 class="text-base font-semibold">Défense Sicilienne (Déjà suivi)</h3>
                            <div class="flex gap-1 -mt-1">
                                <button class="text-green-600 hover:text-green-800">
                                    <CheckIcon class="h-5 w-5" />
                                </button>
                                <a href="#" class="text-gray-600 hover:text-gray-800">
                                    <ArrowTopRightOnSquareIcon class="h-5 w-5" />
                                </a>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">8 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les noirs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Garry Kasparov</span>
                    </template>
                    <p class="line-clamp-2 mb-2">Une ouverture d'échecs hyperagressive pour les noirs.</p>
                </Card>
            </div>
        </div>

        <!-- Example 5: Card footer -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">5. Pied de carte avec boutons</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1010/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">La défense française (Non suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">12 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les blancs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Eugene Znosko-Borovsky</span>
                    </template>
                    <p class="line-clamp-2 mb-2">La manière la plus intransigeante d'attaquer la défense française.</p>
                    <!-- Footer action bar -->
                    <div class="flex justify-end gap-2 pt-3 mt-3 border-t border-gray-200">
                        <button
                            class="px-3 py-1 rounded-md bg-blue-100 text-blue-700 text-sm font-medium hover:bg-blue-200 flex items-center gap-1">
                            <PlusIcon class="h-4 w-4" />
                            <span>Suivre</span>
                        </button>
                        <button
                            class="px-3 py-1 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 flex items-center gap-1">
                            <InformationCircleIcon class="h-4 w-4" />
                            <span>Détails</span>
                        </button>
                    </div>
                </Card>

                <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                    <template #image>
                        <img src="https://picsum.photos/id/1025/600/600" alt="Pièce d'échecs"
                            class="w-full h-full object-cover" />
                    </template>
                    <template #indicators>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                    </template>
                    <template #title>
                        <h3 class="text-base font-semibold">Défense Sicilienne (Déjà suivi)</h3>
                        <div class="text-xs text-gray-500 mt-1">8 chapitres</div>
                        <div class="text-xs text-gray-500">
                            <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                        </div>
                    </template>
                    <template #colorside>
                        <span class="text-xs text-gray-500">Pour les noirs</span>
                    </template>
                    <template #author>
                        <span class="text-xs text-gray-500">Par Garry Kasparov</span>
                    </template>
                    <p class="line-clamp-2 mb-2">Une ouverture d'échecs hyperagressive pour les noirs.</p>
                    <!-- Footer action bar -->
                    <div class="flex justify-end gap-2 pt-3 mt-3 border-t border-gray-200">
                        <button
                            class="px-3 py-1 rounded-md bg-green-100 text-green-700 text-sm font-medium hover:bg-green-200 flex items-center gap-1">
                            <CheckIcon class="h-4 w-4" />
                            <span>Suivi</span>
                        </button>
                        <a href="#"
                            class="px-3 py-1 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 flex items-center gap-1">
                            <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                            <span>Accéder</span>
                        </a>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Example 6: Slide-out action tray -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">6. Barre d'action au survol</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group relative">
                    <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                        <template #image>
                            <img src="https://picsum.photos/id/1010/600/600" alt="Pièce d'échecs"
                                class="w-full h-full object-cover" />
                        </template>
                        <template #indicators>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                        </template>
                        <template #title>
                            <h3 class="text-base font-semibold">La défense française (Non suivi)</h3>
                            <div class="text-xs text-gray-500 mt-1">12 chapitres</div>
                            <div class="text-xs text-gray-500">
                                <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                            </div>
                        </template>
                        <template #colorside>
                            <span class="text-xs text-gray-500">Pour les blancs</span>
                        </template>
                        <template #author>
                            <span class="text-xs text-gray-500">Par Eugene Znosko-Borovsky</span>
                        </template>
                        <p class="line-clamp-2 mb-2">La manière la plus intransigeante d'attaquer la défense française.
                        </p>
                    </Card>
                    <!-- Hover slide-out actions -->
                    <div
                        class="absolute right-0 top-0 bottom-0 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-center -mr-16 group-hover:mr-0">
                        <button
                            class="mb-2 w-12 h-12 rounded-l-md bg-blue-500 text-white flex items-center justify-center shadow-md hover:bg-blue-600">
                            <PlusIcon class="h-6 w-6" />
                        </button>
                        <button
                            class="w-12 h-12 rounded-l-md bg-gray-600 text-white flex items-center justify-center shadow-md hover:bg-gray-700">
                            <InformationCircleIcon class="h-6 w-6" />
                        </button>
                    </div>
                </div>

                <div class="group relative">
                    <Card layout="chess" variant="bordered" elevation="sm" className="h-auto">
                        <template #image>
                            <img src="https://picsum.photos/id/1025/600/600" alt="Pièce d'échecs"
                                class="w-full h-full object-cover" />
                        </template>
                        <template #indicators>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                        </template>
                        <template #title>
                            <h3 class="text-base font-semibold">Défense Sicilienne (Déjà suivi)</h3>
                            <div class="text-xs text-gray-500 mt-1">8 chapitres</div>
                            <div class="text-xs text-gray-500">
                                <div>Mis à jour le {{ formatDate(new Date().toISOString()) }}</div>
                            </div>
                        </template>
                        <template #colorside>
                            <span class="text-xs text-gray-500">Pour les noirs</span>
                        </template>
                        <template #author>
                            <span class="text-xs text-gray-500">Par Garry Kasparov</span>
                        </template>
                        <p class="line-clamp-2 mb-2">Une ouverture d'échecs hyperagressive pour les noirs.</p>
                    </Card>
                    <!-- Hover slide-out actions -->
                    <div
                        class="absolute right-0 top-0 bottom-0 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-center -mr-16 group-hover:mr-0">
                        <button
                            class="mb-2 w-12 h-12 rounded-l-md bg-green-500 text-white flex items-center justify-center shadow-md hover:bg-green-600">
                            <CheckIcon class="h-6 w-6" />
                        </button>
                        <a href="#"
                            class="w-12 h-12 rounded-l-md bg-gray-600 text-white flex items-center justify-center shadow-md hover:bg-gray-700">
                            <ArrowTopRightOnSquareIcon class="h-6 w-6" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Card from '../components/ui/Card.vue'
import {
    PlusIcon,
    CheckIcon,
    InformationCircleIcon,
    ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline'

// Format date for display
const formatDate = (dateString: string) => {
    if (!dateString) return 'Date inconnue'

    const date = new Date(dateString)
    return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date)
}
</script>