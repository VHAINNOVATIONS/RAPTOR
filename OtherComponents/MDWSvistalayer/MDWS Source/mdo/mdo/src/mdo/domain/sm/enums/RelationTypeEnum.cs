using System;

namespace gov.va.medora.mdo.domain.sm.enums
{
    [Serializable]
    public enum RelationTypeEnum
    {
        PRIMARY_PROVIDER = 0, // patient associated to PCMM
        CLINIC = 1, // patient associated to clinic
        TEAM = 2, // patient associated to OE/RR team
        FACILITY = 3,
        VISN = 4,
        GLOBAL = 5,
        PATIENT = 6 // patient manually associated to triage group
    }
}
