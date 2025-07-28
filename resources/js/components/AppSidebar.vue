<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
  LayoutGrid,
  Bot,
  MapPin,
  BarChart3,
  AlertTriangle,
  Clock,
  Settings,
  Users,
  Folder,
  BookOpen,
  Route,
  DatabaseBackup
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Map View',
    href: '/map_view',
    icon: MapPin,
  },
  {
    title: 'Road Monitoring',
    href: '/road_monitoring',
    icon: Route,
  },
  {
    title: 'Vehicle Analytics',
    href: '/vehicle_analytics',
    icon: BarChart3,
  },
  {
    title: 'Maintenance Alerts',
    href: '/maintenance_alert',
    icon: AlertTriangle,
  },
  {
    title: 'Historical Data',
    href: '/historical_data',
    icon: Clock,
  },
  {
    title: 'User Management',
    href: '/users',
    icon: Users,
  },
  {
    title: 'Backup and Restore',
    href: '/backup_and_restore',
    icon: DatabaseBackup,
  },
];
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="route('dashboard')">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
