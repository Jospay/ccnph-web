// seller
export interface SummaryChartSegment {
  key: string;
  label: string;
  value: number;
}
export interface ProductsSummary {
  total: number;
  totalViews: number;
  chart: SummaryChartSegment[];
}
export interface OrdersSummary {
  total: number;
  chart: SummaryChartSegment[];
}
export interface SalesSummary {
  totalAmount: number;
  total: number;
  chart: SummaryChartSegment[];
}
// seller sales
export interface SalesSummaryStat {
  today: number;
  weekly: number;
  monthly: number;
  yearly: number;
}
export interface TopProduct {
  key: string;
  label: string;
  value: number;
}
export interface OrdersOverviewPoint {
  month: string;
  label: string;
  orders: number;
}
export interface OrderStatusSlice {
  key: string;
  label: string;
  value: number;
  color: string;
}
