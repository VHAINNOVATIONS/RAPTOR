using System;
using gov.va.medora.mdo;
using System.Collections.Generic;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class RadiologyCancellationReasonTO : AbstractTO
    {
        public String id;
        public String name;
        public String type;
        public String synonym;

        public RadiologyCancellationReasonTO() { }

        public RadiologyCancellationReasonTO(RadiologyCancellationReason reason)
        {
            this.id = reason.Id;
            this.name = reason.Reason;
            this.type = reason.Type;
            this.synonym = reason.Synonym;
        }
    }

    public class RadiologyCancellationReasonArray : AbstractArrayTO
    {
        public RadiologyCancellationReasonTO[] reasons;

        public RadiologyCancellationReasonArray() { }

        public RadiologyCancellationReasonArray(IList<RadiologyCancellationReason> mdos)
        {
            this.count = mdos.Count;
            IList<RadiologyCancellationReasonTO> tmp = new List<RadiologyCancellationReasonTO>();
            foreach (RadiologyCancellationReason reason in mdos)
            {
                tmp.Add(new RadiologyCancellationReasonTO(reason));
            }
            this.reasons = new RadiologyCancellationReasonTO[tmp.Count];
            tmp.CopyTo(this.reasons, 0);
        }
    }
}