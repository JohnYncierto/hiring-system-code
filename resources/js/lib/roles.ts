export type RoleOption = { value: string; label: string };

export const ROLE_OPTIONS: RoleOption[] = [
    { value: 'admin', label: 'Admin' },
    { value: 'recruiter', label: 'Recruiter' },
    { value: 'hiring_manager', label: 'Hiring Manager' },
    { value: 'interviewer', label: 'Interviewer' },
];

export function roleLabel(value: string): string {
    return ROLE_OPTIONS.find((r) => r.value === value)?.label ?? value;
}