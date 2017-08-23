using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdws.dto
{
    public class TaggedDemographicsRecordArray : AbstractTaggedArrayTO
    {
        public DemographicsRecord[] rex;

        public TaggedDemographicsRecordArray() { }

        public TaggedDemographicsRecordArray(string tag, DemographicsRecord[] rex)
        {
            this.tag = tag;
            if (rex == null)
            {
                this.count = 0;
                return;
            }
            this.rex = rex;
            this.count = rex.Length;
        }

        public TaggedDemographicsRecordArray(string tag, List<DemographicsRecord> rex)
        {
            this.tag = tag;
            if (rex == null)
            {
                this.count = 0;
                return;
            }
            this.rex = new DemographicsRecord[rex.Count];
            for (int i = 0; i < rex.Count; i++)
            {
                this.rex[i] = rex[i];
            }
            this.count = rex.Count;
        }

        public TaggedDemographicsRecordArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedDemographicsRecordArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
