using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedObservationArray : AbstractTaggedArrayTO
    {
        public LabObservationTO[] observations;

        public TaggedObservationArray() { }

        public TaggedObservationArray(string tag, LabObservation[] observations)
        {
            this.tag = tag;
            if (observations == null)
            {
                this.count = 0;
                return;
            }
            this.observations = new LabObservationTO[observations.Length];
            for (int i = 0; i < observations.Length; i++)
            {
                this.observations[i] = new LabObservationTO(observations[i]);
            }
            this.count = observations.Length;
        }

        public TaggedObservationArray(string tag, LabObservation observation)
        {
            this.tag = tag;
            if (observation == null)
            {
                this.count = 0;
                return;
            }
            this.observations = new LabObservationTO[1];
            this.observations[0] = new LabObservationTO(observation);
            this.count = 1;
        }

        public TaggedObservationArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }
    }
}
