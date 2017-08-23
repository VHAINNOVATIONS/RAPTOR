using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedNoteArray : AbstractTaggedArrayTO
    {
        public NoteTO[] notes;

        public TaggedNoteArray() { }

        public TaggedNoteArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedNoteArray(string tag, Note[] notes)
        {
            this.tag = tag;
            if (notes == null || notes.Length == 0)
            {
                this.count = 0;
                return;
            }
            this.notes = new NoteTO[notes.Length];
            for (int i = 0; i < notes.Length; i++)
            {
                this.notes[i] = new NoteTO(notes[i]);
            }
            this.count = notes.Length;
        }

        public TaggedNoteArray(string tag, Note note)
        {
            this.tag = tag;
            if (note == null)
            {
                this.count = 0;
                return;
            }
            this.notes = new NoteTO[1];
            this.notes[0] = new NoteTO(note);
            this.count = 1;
        }

        public TaggedNoteArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
