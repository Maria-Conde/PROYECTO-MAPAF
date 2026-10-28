<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño REPOSITORY: Interfaz Base
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Centraliza y abstrae el acceso a datos para desacoplar las consultas SQL
 * de la lógica de negocio y permitir resiliencia con fallback en memoria.
 */

interface RepositoryInterface {
    public function getAll(): array;
    public function getById(string $id): ?array;
}
