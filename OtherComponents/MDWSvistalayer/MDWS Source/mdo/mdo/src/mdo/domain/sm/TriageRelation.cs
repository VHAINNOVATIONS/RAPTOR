using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    public class TriageRelation : BaseModel 
    {
	    public RelationTypeEnum RelationType { get; set; }
	    public string Name { get; set; }
	    public string StationNumber { get; set; }
	    public string VistaIen { get; set; }
	    public TriageGroup TriageGroup { get; set; }
	    public List<gov.va.medora.mdo.domain.sm.Patient> Patients { get; set; }
    }
}
